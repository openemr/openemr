/**
 * Custom release-please updater for OpenEMR's version.php file.
 *
 * This updater handles the unique version format in version.php:
 * - $v_major, $v_minor, $v_patch for the main version (e.g., 8.0.1)
 * - $v_realpatch for hotfix patches (e.g., 8.0.1 patch 2)
 * - $v_tag for pre-release tags (e.g., '-dev', '-rc1')
 *
 * For production releases, $v_tag is cleared (set to empty string).
 */

class VersionPhpUpdater {
  /**
   * Creates a new VersionPhpUpdater
   * @param {object} options - The options passed from release-please config
   */
  constructor(options) {
    this.options = options || {};
  }

  /**
   * Read the version from version.php content
   * @param {string} content - The file content
   * @returns {string} The version string (e.g., "8.0.1")
   */
  readVersion(content) {
    const majorMatch = content.match(/\$v_major\s*=\s*['"](\d+)['"]/);
    const minorMatch = content.match(/\$v_minor\s*=\s*['"](\d+)['"]/);
    const patchMatch = content.match(/\$v_patch\s*=\s*['"](\d+)['"]/);

    if (!majorMatch || !minorMatch || !patchMatch) {
      throw new Error('Could not parse version from version.php');
    }

    return `${majorMatch[1]}.${minorMatch[1]}.${patchMatch[1]}`;
  }

  /**
   * Update version.php content with a new version
   * @param {string} content - The current file content
   * @param {string} version - The new version string (e.g., "8.0.1")
   * @returns {string} The updated file content
   */
  updateContent(content, version) {
    // Parse version string (supports 3 or 4 part versions)
    const versionParts = version.split('.');
    if (versionParts.length < 3) {
      throw new Error(`Invalid version format: ${version}. Expected at least X.Y.Z`);
    }

    const major = versionParts[0];
    const minor = versionParts[1];
    const patch = versionParts[2];
    // If 4-part version (e.g., 8.0.1.2), the 4th part is the realpatch
    const realpatch = versionParts[3] || '0';

    let updatedContent = content;

    // Update $v_major
    updatedContent = updatedContent.replace(
      /(\$v_major\s*=\s*['"])\d+(['"])/,
      `$1${major}$2`
    );

    // Update $v_minor
    updatedContent = updatedContent.replace(
      /(\$v_minor\s*=\s*['"])\d+(['"])/,
      `$1${minor}$2`
    );

    // Update $v_patch
    updatedContent = updatedContent.replace(
      /(\$v_patch\s*=\s*['"])\d+(['"])/,
      `$1${patch}$2`
    );

    // Update $v_realpatch
    updatedContent = updatedContent.replace(
      /(\$v_realpatch\s*=\s*['"])\d+(['"])/,
      `$1${realpatch}$2`
    );

    // Clear $v_tag for production releases (set to empty string)
    updatedContent = updatedContent.replace(
      /(\$v_tag\s*=\s*')[^']*(')/,
      "$1$2"
    );

    return updatedContent;
  }
}

module.exports = VersionPhpUpdater;
