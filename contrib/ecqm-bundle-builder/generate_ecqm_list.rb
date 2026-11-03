#!/usr/bin/env ruby
# frozen_string_literal: true

# Writes the SQL for a performance period's eCQM list (list_options
# ecqm_{year}_reporting), the measures OpenEMR's QRDA screens offer. Each
# measure's option_id is its cms_id, which is also its directory in the
# bundle. The SQL is guarded so it can go in an upgrade script as it is.
#
# @package   OpenEMR
# @link      https://www.open-emr.org
# @author    Jerry Padgett <sjpadgett@gmail.com>
# @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
# @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3

require 'json'
require 'fileutils'
require 'optparse'
require 'cgi'

options = {
  year: nil,
  bundle_dir: ENV.fetch('BUNDLE_DIR', '.'),
  output_dir: nil,
  # Active by default: the originally certified measures plus CMS68 and
  # CMS1056, as in the shipped lists; matched by CMS id without its version
  defaults: %w[CMS22v CMS68v CMS69v CMS122v CMS124v CMS125v CMS127v CMS130v CMS138v CMS147v CMS165v CMS1056v]
}

OptionParser.new do |opts|
  opts.banner = 'Usage: ruby generate_ecqm_list.rb --year YEAR [options]'
  opts.on('--year YEAR', Integer, 'Performance period') { |v| options[:year] = v }
  opts.on('--bundle-dir DIR', 'Directory holding the {year}_reporting_period folders (default: $BUNDLE_DIR)') { |v| options[:bundle_dir] = v }
  opts.on('--output-dir DIR', 'Where to write the SQL (default: the bundle directory)') { |v| options[:output_dir] = v }
  opts.on('--defaults x,y,z', Array, 'CMS ids, without version (e.g. CMS122v), active by default') { |v| options[:defaults] = v }
end.parse!

abort('[ERROR] --year is required') unless options[:year]

list_id = "ecqm_#{options[:year]}_reporting"
list_title = "eCQM #{options[:year]} Performance Period"
json_dir = File.join(options[:bundle_dir], "#{options[:year]}_reporting_period", 'json_measures')
abort("[ERROR] #{json_dir} not found") unless Dir.exist?(json_dir)

def sql_string(value)
  "'" + value.to_s.gsub('\\', '\\\\\\\\').gsub("'", "\\\\'") + "'"
end

measures = []
Dir.glob(File.join(json_dir, '*')).select { |d| File.directory?(d) }.each do |dir|
  file = File.join(dir, "#{File.basename(dir)}.json")
  unless File.exist?(file)
    warn "[WARN] #{dir} has no #{File.basename(file)}; run build_bundle.rb --rename-existing"
    next
  end
  measure = JSON.parse(File.read(file), max_nesting: false)
  if measure['cms_id'] != File.basename(dir)
    warn "[WARN] #{dir} holds #{measure['cms_id']}; run build_bundle.rb --rename-existing"
    next
  end
  measures << { id: measure['cms_id'], title: measure['title'].to_s, description: measure['description'].to_s }
end
abort("[ERROR] No measures found in #{json_dir}") if measures.empty?

# Numeric CMS order (CMS2, CMS22, CMS122, ...) so the list and the SQL are stable
measures.sort_by! { |m| [m[:id][/\d+/].to_i, m[:id]] }

sql = []
sql << "#IfNotRow list_options list_id #{list_id}"
sql << 'INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `is_default`, `option_value`, `notes`) VALUES ' \
       "('lists',#{sql_string(list_id)},#{sql_string(list_title)},0,1,0,'');"
measures.each_with_index do |m, index|
  active = options[:defaults].any? { |prefix| m[:id].start_with?(prefix) } ? 1 : 0
  notes = CGI.escapeHTML(m[:description]).gsub("\n", '<br>')
  sql << 'INSERT INTO `list_options` (`list_id`, `option_id`, `title`, `seq`, `activity`, `notes`) VALUES ' \
         "(#{sql_string(list_id)},#{sql_string(m[:id])},#{sql_string(m[:title])},#{(index + 1) * 10},#{active},#{sql_string(notes)});"
  puts "[INFO] #{m[:id]}#{active == 1 ? ' (active)' : ''} - #{m[:title]}"
end
sql << '#EndIf'

output_dir = options[:output_dir] || options[:bundle_dir]
FileUtils.mkdir_p(output_dir)
path = File.join(output_dir, "#{list_id}.sql")
File.write(path, sql.join("\n") + "\n")
puts "[INFO] #{measures.length} measures written to #{path}"
