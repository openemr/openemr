<?php

/**
 * Vietnamese Test Data Fixtures
 * Provides sample data for Vietnamese physiotherapy tests
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2024 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Fixtures\Vietnamese;

class VietnameseTestData
{
    /**
     * Sample patient names (Vietnamese)
     */
    public static function getPatientNames(): array
    {
        return [
            ['vi' => 'Nguyễn Văn An', 'en' => 'An Nguyen Van'],
            ['vi' => 'Trần Thị Bình', 'en' => 'Binh Tran Thi'],
            ['vi' => 'Lê Văn Cường', 'en' => 'Cuong Le Van'],
            ['vi' => 'Phạm Thị Dung', 'en' => 'Dung Pham Thi'],
            ['vi' => 'Hoàng Văn Em', 'en' => 'Em Hoang Van'],
        ];
    }

    /**
     * Medical terminology dictionary
     */
    public static function getMedicalTerms(): array
    {
        return [
            'general' => [
                'Vật lý trị liệu' => 'Physiotherapy',
                'Bệnh nhân' => 'Patient',
                'Điều trị' => 'Treatment',
                'Đánh giá' => 'Assessment',
                'Chẩn đoán' => 'Diagnosis',
                'Phục hồi chức năng' => 'Rehabilitation',
            ],
            'conditions' => [
                'Đau lưng' => 'Back pain',
                'Đau cổ' => 'Neck pain',
                'Đau vai' => 'Shoulder pain',
                'Đau đầu gối' => 'Knee pain',
                'Đau cơ xương khớp' => 'Musculoskeletal pain',
                'Gãy xương' => 'Fracture',
                'Bong gân' => 'Sprain',
                'Viêm khớp' => 'Arthritis',
                'Thoái hóa khớp' => 'Osteoarthritis',
                'Viêm gân' => 'Tendinitis',
            ],
            'treatments' => [
                'Massage' => 'Massage',
                'Vận động trị liệu' => 'Exercise therapy',
                'Điện trị liệu' => 'Electrotherapy',
                'Nhiệt trị liệu' => 'Thermotherapy',
                'Thủy trị liệu' => 'Hydrotherapy',
                'Kéo giãn' => 'Stretching',
                'Tập phục hồi' => 'Rehabilitation exercise',
                'Châm cứu' => 'Acupuncture',
                'Bấm huyệt' => 'Acupressure',
            ],
            'body_parts' => [
                'Cột sống' => 'Spine',
                'Vai' => 'Shoulder',
                'Khuỷu tay' => 'Elbow',
                'Cổ tay' => 'Wrist',
                'Hông' => 'Hip',
                'Đầu gối' => 'Knee',
                'Cổ chân' => 'Ankle',
                'Cơ' => 'Muscle',
                'Xương' => 'Bone',
                'Khớp' => 'Joint',
                'Dây chằng' => 'Ligament',
                'Gân' => 'Tendon',
            ],
        ];
    }

    /**
     * Sample assessments
     */
    public static function getSampleAssessments(): array
    {
        return [
            [
                'patient_name_vi' => 'Nguyễn Văn An',
                'patient_name_en' => 'An Nguyen Van',
                'chief_complaint_vi' => 'Đau lưng dưới kéo dài 3 tháng',
                'chief_complaint_en' => 'Lower back pain for 3 months',
                'diagnosis_vi' => 'Thoái hóa đốt sống thắt lưng',
                'diagnosis_en' => 'Lumbar spondylosis',
                'treatment_plan_vi' => 'Vận động trị liệu, massage, điện trị liệu',
                'treatment_plan_en' => 'Exercise therapy, massage, electrotherapy',
            ],
            [
                'patient_name_vi' => 'Trần Thị Bình',
                'patient_name_en' => 'Binh Tran Thi',
                'chief_complaint_vi' => 'Đau vai phải sau chấn thương',
                'chief_complaint_en' => 'Right shoulder pain after injury',
                'diagnosis_vi' => 'Viêm bao hoạt dịch vai',
                'diagnosis_en' => 'Shoulder bursitis',
                'treatment_plan_vi' => 'Nghỉ ngơi, chườm lạnh, massage nhẹ',
                'treatment_plan_en' => 'Rest, cold compress, gentle massage',
            ],
        ];
    }

    /**
     * Sample exercise prescriptions
     */
    public static function getExercisePrescriptions(): array
    {
        return [
            [
                'exercise_name_vi' => 'Kéo giãn cơ vai',
                'exercise_name_en' => 'Shoulder stretch',
                'instructions_vi' => 'Thực hiện 10 lần, 3 hiệp/ngày',
                'instructions_en' => 'Perform 10 reps, 3 sets/day',
                'duration_weeks' => 4,
                'frequency_per_week' => 5,
            ],
            [
                'exercise_name_vi' => 'Gập bụng tăng cường',
                'exercise_name_en' => 'Core strengthening',
                'instructions_vi' => 'Giữ 30 giây, 5 lần/hiệp, 2 hiệp/ngày',
                'instructions_en' => 'Hold 30 seconds, 5 reps/set, 2 sets/day',
                'duration_weeks' => 6,
                'frequency_per_week' => 7,
            ],
        ];
    }

    /**
     * Sample outcome measures
     */
    public static function getOutcomeMeasures(): array
    {
        return [
            [
                'measure_name_vi' => 'Thang đo đau VAS',
                'measure_name_en' => 'VAS Pain Scale',
                'score_initial' => 8,
                'score_current' => 4,
                'score_target' => 2,
                'notes_vi' => 'Tiến triển tốt, giảm đau đáng kể',
                'notes_en' => 'Good progress, significant pain reduction',
            ],
            [
                'measure_name_vi' => 'Biên độ chuyển động vai',
                'measure_name_en' => 'Shoulder Range of Motion',
                'score_initial' => 90,
                'score_current' => 135,
                'score_target' => 170,
                'notes_vi' => 'Cải thiện dần, cần tiếp tục tập',
                'notes_en' => 'Gradual improvement, need to continue exercises',
            ],
        ];
    }

    /**
     * Sample insurance information
     */
    public static function getInsuranceData(): array
    {
        return [
            [
                'insurance_company_vi' => 'Bảo Hiểm Xã Hội Việt Nam',
                'insurance_company_en' => 'Vietnam Social Security',
                'policy_number' => 'VN123456789',
                'coverage_type_vi' => 'Bảo hiểm y tế toàn diện',
                'coverage_type_en' => 'Comprehensive health insurance',
            ],
            [
                'insurance_company_vi' => 'Bảo Hiểm Prudential',
                'insurance_company_en' => 'Prudential Insurance',
                'policy_number' => 'PRU987654321',
                'coverage_type_vi' => 'Bảo hiểm sức khỏe cao cấp',
                'coverage_type_en' => 'Premium health insurance',
            ],
        ];
    }

    /**
     * Vietnamese text samples for testing
     */
    public static function getVietnameseTextSamples(): array
    {
        return [
            'Vật lý trị liệu là phương pháp điều trị không dùng thuốc',
            'Bệnh nhân cần được đánh giá toàn diện trước khi điều trị',
            'Phục hồi chức năng sau chấn thương đòi hỏi kiên trì và nỗ lực',
            'Massage trị liệu giúp giảm đau và thư giãn cơ bắp',
            'Tập vật lý trị liệu đều đặn sẽ mang lại hiệu quả tốt',
        ];
    }

    /**
     * Vietnamese character sets for validation
     */
    public static function getVietnameseCharacterSets(): array
    {
        return [
            'vowels_a' => ['a', 'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'],
            'vowels_e' => ['e', 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'],
            'vowels_i' => ['i', 'í', 'ì', 'ỉ', 'ĩ', 'ị'],
            'vowels_o' => ['o', 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'],
            'vowels_u' => ['u', 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'],
            'vowels_y' => ['y', 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'],
            'special' => ['đ', 'Đ'],
        ];
    }

    /**
     * Date/time formats for Vietnam
     */
    public static function getDateTimeFormats(): array
    {
        return [
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'datetime_format' => 'd/m/Y H:i',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'locale' => 'vi_VN.UTF-8',
        ];
    }
}