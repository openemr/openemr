#!/usr/bin/env ruby
# frozen_string_literal: true

require 'fileutils'
require 'json'
require 'minitest/autorun'
require 'open3'
require 'rbconfig'
require 'tmpdir'

class GenerateEcqmListTest < Minitest::Test
  def test_sql_is_guarded_sorted_and_escapes_measure_text
    Dir.mktmpdir do |bundle_dir|
      measure_dir = File.join(bundle_dir, '2026_reporting_period', 'json_measures')
      write_measure(measure_dir, 'CMS122v14', "A measure's title", "A <note>\nsecond line")
      write_measure(measure_dir, 'CMS2v1', 'Another measure', '')

      stdout, stderr, status = Open3.capture3(
        RbConfig.ruby,
        File.join(__dir__, 'generate_ecqm_list.rb'),
        '--year', '2026',
        '--bundle-dir', bundle_dir,
        '--defaults', 'CMS122v',
      )

      assert_predicate status, :success?, stderr
      assert_includes stdout, '[INFO] 2 measures written'
      sql = File.read(File.join(bundle_dir, 'ecqm_2026_reporting.sql'))
      assert_includes sql, "#IfNotRow list_options list_id ecqm_2026_reporting"
      assert_operator sql.index("'CMS2v1'"), :<, sql.index("'CMS122v14'")
      assert_includes sql, "A measure\\'s title"
      assert_includes sql, 'A &lt;note&gt;<br>second line'
      assert_match(/'CMS122v14'.*,20,1,/, sql)
      assert_match(/'CMS2v1'.*,10,0,/, sql)
      assert sql.end_with?("#EndIf\n")
    end
  end

  def test_empty_measure_directory_fails_instead_of_writing_empty_list
    Dir.mktmpdir do |bundle_dir|
      FileUtils.mkdir_p(File.join(bundle_dir, '2026_reporting_period', 'json_measures'))

      _stdout, stderr, status = Open3.capture3(
        RbConfig.ruby,
        File.join(__dir__, 'generate_ecqm_list.rb'),
        '--year', '2026',
        '--bundle-dir', bundle_dir,
      )

      refute_predicate status, :success?
      assert_includes stderr, 'No measures found'
    end
  end

  private

  def write_measure(json_dir, cms_id, title, description)
    measure_dir = File.join(json_dir, cms_id)
    FileUtils.mkdir_p(measure_dir)
    File.write(
      File.join(measure_dir, "#{cms_id}.json"),
      JSON.generate('cms_id' => cms_id, 'title' => title, 'description' => description),
    )
  end
end
