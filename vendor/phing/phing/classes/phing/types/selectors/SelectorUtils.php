<?php

/*
 * $Id: e131f3a155ff70d247bcd93d12d7f5f1a1f45977 $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

include_once 'phing/util/StringHelper.php';

/**
 * <p>This is a utility class used by selectors and DirectoryScanner. The
 * functionality more properly belongs just to selectors, but unfortunately
 * DirectoryScanner exposed these as protected methods. Thus we have to
 * support any subclasses of DirectoryScanner that may access these methods.
 * </p>
 * <p>This is a Singleton.</p>
 *
 * @author Hans Lellelid, hans@xmpl.org (Phing)
 * @author Arnout J. Kuiper, ajkuiper@wxs.nl (Ant)
 * @author Magesh Umasankar
 * @author Bruce Atherton, bruce@callenish.com (Ant)
 * @package phing.types.selectors
 */
class SelectorUtils
{

    private static $instance;

    /**
     * Retrieves the instance of the Singleton.
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new SelectorUtils();
        }

        return self::$instance;
    }

    /**
     * Tests whether or not a given path matches the start of a given
     * pattern up to the first "**".
     * <p>
     * This is not a general purpose test and should only be used if you
     * can live with false positives. For example, <code>pattern=**\a</code>
     * and <code>str=b</code> will yield <code>true</code>.
     *
     * @param string $pattern
     * @param string $str
     * @param bool $isCaseSensitive
     *
     * @internal param The $pattern pattern to match against. Must not be
     *                <code>null</code>.
     * @internal param The $str path to match, as a String. Must not be
     *                <code>null</code>.
     * @internal param Whether $isCaseSensitive or not matching should be performed
     *                        case sensitively.
     *
     * @return bool whether or not a given path matches the start of a given
     *                 pattern up to the first "**".
     */
    public static function matchPatternStart($pattern, $str, $isCaseSensitive = true)
    {

        // When str starts with a DIRECTORY_SEPARATOR, pattern has to start with a
        // DIRECTORY_SEPARATOR.
        // When pattern starts with a DIRECTORY_SEPARATOR, str has to start with a
        // DIRECTORY_SEPARATOR.
        if (StringHelper::startsWith(DIRECTORY_SEPARATOR, $str) !==
            StringHelper::startsWith(DIRECTORY_SEPARATOR, $pattern)
        ) {
            return false;
        }

        $patDirs = explode(DIRECTORY_SEPARATOR, $pattern);
        $strDirs = explode(DIRECTORY_SEPARATOR, $str);

        $patIdxStart = 0;
        $patIdxEnd = count($patDirs) - 1;
        $strIdxStart = 0;
        $strIdxEnd = count($strDirs) - 1;

        // up to first '**'
        while ($patIdxStart <= $patIdxEnd && $strIdxStart <= $strIdxEnd) {
            $patDir = $patDirs[$patIdxStart];
            if ($patDir == "**") {
                break;
            }
            if (!self::match($patDir, $strDirs[$strIdxStart], $isCaseSensitive)) {
                return false;
            }
            $patIdxStart++;
            $strIdxStart++;
        }

        if ($strIdxStart > $strIdxEnd) {
            // String is exhausted
            return true;
        } elseif ($patIdxStart > $patIdxEnd) {
            // String not exhausted, but pattern is. Failure.
            return false;
        } else {
            // pattern now holds ** while string is not exhausted
            // this will generate false positives but we can live with that.
            return true;
        }
    }

    /**
     * Tests whether or not a given path matches a given pattern.
     *
     * @param The $pattern
     * @param The $str
     * @param bool|Whether $isCaseSensitive
     * @internal param The $pattern pattern to match against. Must not be
     *                <code>null</code>.
     * @internal param The $str path to match, as a String. Must not be
     *                <code>null</code>.
     * @internal param Whether $isCaseSensitive or not matching should be performed
     *                        case sensitively.
     *
     * @return bool <code>true</code> if the pattern matches against the string,
     */
    public static function matchPath($pattern, $str, $isCaseSensitive = true)
    {
        // explicitly exclude directory itself
        if ($str == '' && $pattern == '**/*') {
            return false;
        }

        $rePattern = preg_quote($pattern, '/');
        $dirSep = preg_quote(DIRECTORY_SEPARATOR, '/');
        $trailingDirSep = '((' . $dirSep . ')?|(' . $dirSep . ').+)';
        $patternReplacements = array(
            $dirSep . '\*\*' . $dirSep => $dirSep . '.*' . $trailingDirSep,
            $dirSep . '\*\*' => $trailingDirSep,
            '\*\*' . $dirSep => '.*' . $trailingDirSep,
            '\*\*' => '.*',
            '\*' => '[^' . $dirSep . ']*',
            '\?' => '[^' . $dirSep . ']'
        );
        $rePattern = str_replace(array_keys($patternReplacements), array_values($patternReplacements), $rePattern);
        $rePattern = '/^' . $rePattern . '$/' . ($isCaseSensitive ? '' : 'i');

        return (bool) preg_match($rePattern, $str);
    }

    /**
     * Tests whether or not a string matches against a pattern.
     * The pattern may contain two special characters:<br>
     * '*' means zero or more characters<br>
     * '?' means one and only one character
     *
     * @param string $pattern The pattern to match against.
     *                Must not be <code>null</code>.
     * @param string $str     The string which must be matched against the pattern.
     *                Must not be <code>null</code>.
     * @param bool $isCaseSensitive Whether or not matching should be performed
     *                        case sensitively.
     *
     *
     * @return bool <code>true</code> if the string matches against the pattern,
     *                           or <code>false</code> otherwise.
     */
    public static function match($pattern, $str, $isCaseSensitive = true)
    {
        $rePattern = preg_quote($pattern, '/');
        $rePattern = str_replace(array("\*", "\?"), array('.*', '.'), $rePattern);
        $rePattern = '/^' . $rePattern . '$/' . ($isCaseSensitive ? '' : 'i');

        return (bool) preg_match($rePattern, $str);
    }

    /**
     * Returns dependency information on these two files. If src has been
     * modified later than target, it returns true. If target doesn't exist,
     * it likewise returns true. Otherwise, target is newer than src and
     * is not out of date, thus the method returns false. It also returns
     * false if the src file doesn't even exist, since how could the
     * target then be out of date.
     *
     * @param  PhingFile $src         the original file
     * @param  PhingFile $target      the file being compared against
     * @param  int       $granularity the amount in seconds of slack we will give in
     *                                determining out of dateness
     * @return bool whether   the target is out of date
     */
    public static function isOutOfDate(PhingFile $src, PhingFile $target, $granularity)
    {
        if (!$src->exists()) {
            return false;
        }
        if (!$target->exists()) {
            return true;
        }
        if (($src->lastModified() - $granularity) > $target->lastModified()) {
            return true;
        }

        return false;
    }
}
