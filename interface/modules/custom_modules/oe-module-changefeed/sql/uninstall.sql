--
-- Change Feed module - uninstall SQL
--
-- The change-capture triggers are dropped by ModuleManagerListener (on disable
-- and reset) BEFORE this table is dropped. Dropping changefeed_log while its
-- triggers still reference it would break writes to the watched tables, so the
-- ordering matters: disable the module (drops triggers) before removing it.
--
-- @package   OpenEMR
-- @link      https://www.open-emr.org
-- @author    Ahmed Armaan <arman.ahmaed@carer.ai>
-- @copyright Copyright (c) 2026 Ahmed Armaan
-- @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
--

DROP TABLE IF EXISTS `changefeed_log`;
