--
-- Dashboard Context Manager Module
-- Uninstall SQL Script
--
-- @package   OpenEMR
-- @link      http://www.open-emr.org
-- @author    Jerry Padgett <sjpadgett@gmail.com>
-- @copyright Copyright (c) 2025 Jerry Padgett <sjpadgett@gmail.com>
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--
-- WARNING: This will permanently delete all dashboard context data!
-- Run this only when completely removing the module.
--

-- Drop tables in correct order (respecting potential foreign key relationships)
DROP TABLE IF EXISTS `dashboard_context_audit_log`;
DROP TABLE IF EXISTS `dashboard_widget_labels`;
DROP TABLE IF EXISTS `dashboard_widget_order`;
DROP TABLE IF EXISTS `dashboard_context_role_defaults`;
DROP TABLE IF EXISTS `dashboard_context_assignments`;
DROP TABLE IF EXISTS `dashboard_context_definitions`;
DROP TABLE IF EXISTS `user_dashboard_context_config`;
DROP TABLE IF EXISTS `user_dashboard_context`;
