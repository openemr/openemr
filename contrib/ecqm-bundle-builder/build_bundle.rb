#!/usr/bin/env ruby
# frozen_string_literal: true

# Builds an OpenEMR eCQM measure bundle for one performance period: each CMS
# eCQM package (zip) in {year}_reporting_period/cms_measures becomes
# {year}_reporting_period/json_measures/{cms_id}/{cms_id}.json (the measure,
# with its CQL compiled as ELM) and value_sets.json (its value sets expanded
# from VSAC with the period's eCQM Annual Update profile).
#
# OpenEMR finds a measure at json_measures/{option_id}/{option_id}.json, and
# the measure list uses the measure's cms_id (e.g. CMS122v14) as option_id, so
# directories are named by cms_id whatever the package file is called.
#
# @package   OpenEMR
# @link      https://www.open-emr.org
# @author    Jerry Padgett <sjpadgett@gmail.com>
# @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
# @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3

require 'bundler/setup'
require 'cqm-parsers'
require 'json'
require 'fileutils'
require 'optparse'

Mongoid.include_type_for_serialization = true

# cqm-parsers reads the VSAC endpoints from this constant
APP_CONFIG = {
  'vsac' => {
    'auth_url' => 'https://vsac.nlm.nih.gov/vsac/ws',
    'content_url' => 'https://vsac.nlm.nih.gov/vsac/svs',
    'utility_url' => 'https://vsac.nlm.nih.gov/vsac'
  }
}.freeze

# The eCQM Annual Update published each May is for the NEXT performance
# period, so a period's value sets come from the previous year's profile.
VSAC_PROFILES = {
  2026 => 'eCQM Update 2025-05-08',
  2025 => 'eCQM Update 2024-05-02',
  2024 => 'eCQM Update 2023-05-04',
  2023 => 'eCQM Update 2022-05-05',
  2022 => 'eCQM Update 2021-05-06'
}.freeze

# Value sets a measure package may declare although the period's profile
# expands them to nothing. cqm-parsers fails the whole measure on an empty
# value set; these are kept empty instead, and the measure is still rejected
# if its logic references one (see referenced_empty_value_sets).
ALLOWED_EMPTY_VALUE_SETS = {
  # CMS157v14 still declares it beside its replacement, Federal
  # Administrative Sex (2.16.840.1.113762.1.4.1021.121), but never uses it
  '2.16.840.1.113762.1.4.1' => 'ONC Administrative Sex (retired)'
}.freeze

options = { year: nil, profile: nil, bundle_dir: ENV.fetch('BUNDLE_DIR', '.'), rename_existing: false, rebuild: false,
            allowed_empty: ALLOWED_EMPTY_VALUE_SETS.keys }

parser = OptionParser.new do |opts|
  opts.banner = 'Usage: ruby build_bundle.rb --year YEAR [options]'
  opts.on('--year YEAR', Integer, "Performance period (#{VSAC_PROFILES.keys.sort.join(', ')})") { |v| options[:year] = v }
  opts.on('--profile PROFILE', 'VSAC expansion profile, overriding the one for --year') { |v| options[:profile] = v }
  opts.on('--bundle-dir DIR', 'Directory holding the {year}_reporting_period folders (default: $BUNDLE_DIR)') { |v| options[:bundle_dir] = v }
  opts.on('--rename-existing', 'Only rename already built measure directories to their cms_id; no VSAC needed') { options[:rename_existing] = true }
  opts.on('--rebuild', 'Build measures whose JSON already exists again') { options[:rebuild] = true }
  opts.on('--allow-empty-value-set OID', 'Keep this value set empty if VSAC expands it to nothing (repeatable);',
          'the measure still fails if its logic uses it') { |v| options[:allowed_empty] << v }
  opts.on('-h', '--help', 'Show this help') do
    puts opts
    puts "\nPerformance period => VSAC profile:"
    VSAC_PROFILES.sort.each { |year, profile| puts "  #{year} => #{profile}" }
    exit
  end
end
parser.parse!

abort("[ERROR] --year is required\n\n#{parser}") unless options[:year]

period_dir = File.join(options[:bundle_dir], "#{options[:year]}_reporting_period")
packages_dir = File.join(period_dir, 'cms_measures')
output_dir = File.join(period_dir, 'json_measures')

# The measure JSON file in a measure directory (named for the directory in
# every bundle so far).
def measure_file(dir)
  named = File.join(dir, "#{File.basename(dir)}.json")
  return named if File.exist?(named)

  Dir.glob(File.join(dir, '*.json')).reject { |f| File.basename(f) == 'value_sets.json' }.first
end

# Renames a built measure directory (and its measure file) to its cms_id.
def rename_to_cms_id(dir, output_dir)
  file = measure_file(dir)
  return nil unless file

  cms_id = JSON.parse(File.read(file), max_nesting: false)['cms_id']
  return nil if cms_id.nil? || cms_id.empty? || File.basename(dir) == cms_id

  target = File.join(output_dir, cms_id)
  abort("[ERROR] Cannot rename #{dir}: #{target} already exists") if File.exist?(target)

  FileUtils.mv(dir, target)
  FileUtils.mv(File.join(target, File.basename(file)), File.join(target, "#{cms_id}.json"))
  cms_id
end

if options[:rename_existing]
  abort("[ERROR] #{output_dir} not found") unless Dir.exist?(output_dir)

  renamed = Dir.glob(File.join(output_dir, '*')).select { |d| File.directory?(d) }.sort.filter_map do |dir|
    cms_id = rename_to_cms_id(dir, output_dir)
    puts "[RENAMED] #{File.basename(dir)} -> #{cms_id}" if cms_id
    cms_id
  end
  puts "[SUMMARY] #{renamed.length} measure directories renamed in #{output_dir}"
  exit
end

profile = options[:profile] || VSAC_PROFILES[options[:year]]
abort("[ERROR] No VSAC profile known for #{options[:year]}; pass --profile") unless profile
abort("[ERROR] #{packages_dir} not found; put the CMS eCQM packages (zip) there") unless Dir.exist?(packages_dir)

api_key = ENV.fetch('VSAC_API_KEY', '')
abort('[ERROR] VSAC_API_KEY is not set (see .env.sample)') if api_key.empty?

# cqm-parsers raises VSEmptyError for a value set with no concepts; for the
# allowed OIDs, keep the response so the value set is built with none.
module AllowEmptyValueSets
  class << self
    attr_accessor :oids
  end
  self.oids = []

  private

  def process_and_validate_vsac_response(vs_response, expected_oid)
    super
  rescue Util::VSAC::VSEmptyError
    raise unless AllowEmptyValueSets.oids.include?(expected_oid)

    puts "[WARN] Value set #{expected_oid} is empty in this profile; kept empty"
    vs_response.body.force_encoding('utf-8')
  end
end
AllowEmptyValueSets.oids = options[:allowed_empty].uniq
Util::VSAC::VSACAPI.prepend(AllowEmptyValueSets)

# The empty value sets of a measure that its ELM uses (a ValueSetRef to a
# valueset definition with that OID, in any library). Those would make the
# measure silently miss data, so such a measure fails.
def referenced_empty_value_sets(measure)
  empty = measure.value_sets.select { |vs| vs.concepts.empty? }.map(&:oid)
  return [] if empty.empty?

  measure.cql_libraries.flat_map do |library|
    elm = library.elm.respond_to?(:to_h) ? library.elm.to_h : library.elm
    defs = elm.dig('library', 'valueSets', 'def') || []
    names = defs.select { |d| empty.include?(d['id'].to_s.delete_prefix('urn:oid:')) }.to_h { |d| [d['name'], d['id'].to_s.delete_prefix('urn:oid:')] }
    names.empty? ? [] : value_set_refs(elm).filter_map { |name| names[name] }
  end.uniq
end

def value_set_refs(node, found = [])
  case node
  when Hash
    found << node['name'] if node['type'] == 'ValueSetRef'
    node.each_value { |v| value_set_refs(v, found) }
  when Array
    node.each { |v| value_set_refs(v, found) }
  end
  found
end

value_set_loader = Measures::VSACValueSetLoader.new(options: { profile: profile }, vsac_api_key: api_key)
FileUtils.mkdir_p(output_dir)

# The cms_id a package will produce, from its file name: CMS122v13.zip or
# CMS122-v14.0.000-QDM.zip both give CMS122v...
def expected_cms_id(package)
  name = File.basename(package, '.zip')
  match = name.match(/\A(CMS\d+)-v(\d+)\./) || name.match(/\A(CMS\d+)v(\d+)/)
  match ? "#{match[1]}v#{match[2]}" : nil
end

puts "[INFO] Performance period #{options[:year]}, VSAC profile: #{profile}"
puts "[INFO] Packages: #{packages_dir}"
puts "[INFO] Output:   #{output_dir}"
puts '=' * 60

packages = Dir.glob(File.join(packages_dir, '*.zip')).sort
abort("[ERROR] No CMS measure packages found in #{packages_dir}") if packages.empty?

built = []
skipped = []
failed = {}
packages.each do |package|
  name = File.basename(package, '.zip')
  cms_id = expected_cms_id(package)
  if !options[:rebuild] && cms_id && File.exist?(File.join(output_dir, cms_id, "#{cms_id}.json"))
    skipped << cms_id
    next
  end

  puts "[INFO] Building #{name}"
  begin
    measures = Measures::CqlLoader.new(File.new(package), {}, value_set_loader).extract_measures
    measures.each do |measure|
      used_empty = referenced_empty_value_sets(measure)
      raise "measure logic uses empty value set(s) #{used_empty.join(', ')}" unless used_empty.empty?

      dir = File.join(output_dir, measure.cms_id)
      FileUtils.mkdir_p(dir)
      File.write(File.join(dir, 'value_sets.json'), measure.value_sets.to_json)
      File.write(File.join(dir, "#{measure.cms_id}.json"), measure.to_json)
      built << measure.cms_id
      puts "[BUILT] #{name} -> #{measure.cms_id}"
    end
  rescue Util::VSAC::VSACError => e
    reason = e.message.gsub(api_key, '[REDACTED]')
    failed[name] = "VSAC: #{reason}"
    puts "[ERROR] #{name}: VSAC: #{reason}"
  rescue StandardError => e
    reason = e.message.gsub(api_key, '[REDACTED]')
    failed[name] = "#{e.class}: #{reason}"
    puts "[ERROR] #{name}: #{e.class}: #{reason}"
  end
end

puts '=' * 60
puts "[SUMMARY] built #{built.length}, already built #{skipped.length}, failed #{failed.length}"
unless failed.empty?
  log = File.join(period_dir, "failed_measures_#{Time.now.strftime('%Y%m%d_%H%M%S')}.log")
  File.write(log, failed.map { |name, reason| "#{name}\t#{reason}" }.join("\n") + "\n")
  puts "[SUMMARY] failures logged to #{log}; run again to retry them"
end
exit(failed.empty? ? 0 : 1)
