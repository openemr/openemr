<?php

/**
 * Vietnamese PT Patient Summary Widget
 *
 * Displays recent PT assessments, active exercises, and treatment plans
 * in the patient summary page
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dang Tran <tqvdang@msn.com>
 * @copyright Copyright (c) 2025 Dang Tran
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Services\VietnamesePT\PTAssessmentService;
use OpenEMR\Services\VietnamesePT\PTExercisePrescriptionService;
use OpenEMR\Services\VietnamesePT\PTTreatmentPlanService;

/**
 * Render Vietnamese PT Widget for Patient Summary
 *
 * @param int $patient_id The patient ID
 * @return string HTML output for the widget
 *
 * AI-ENHANCED CODE - START
 * Enhanced with quick stats and recent activity by Claude Code on 2025-11-22
 */
function renderVietnamesePTWidget($patient_id)
{
    if (empty($patient_id)) {
        return '';
    }

    $assessmentService = new PTAssessmentService();
    $exerciseService = new PTExercisePrescriptionService();
    $planService = new PTTreatmentPlanService();

    // Get recent assessments (last 3)
    $assessmentsResult = $assessmentService->getPatientAssessments($patient_id);
    $assessments = $assessmentsResult->hasErrors() ? [] : array_slice($assessmentsResult->getData(), 0, 3);

    // Get active exercises
    $exercisesResult = $exerciseService->getPatientPrescriptions($patient_id);
    $exercises = $exercisesResult->hasErrors() ? [] : array_filter(
        $exercisesResult->getData(),
        function ($ex) {
            return isset($ex['is_active']) && $ex['is_active'] == 1;
        }
    );
    $exercises = array_slice($exercises, 0, 5);

    // Get active treatment plans
    $plansResult = $planService->getActivePlans($patient_id);
    $plans = $plansResult->hasErrors() ? [] : $plansResult->getData();

    // Calculate quick stats
    $totalAssessments = count($assessmentsResult->hasErrors() ? [] : $assessmentsResult->getData());
    $totalExercises = count($exercisesResult->hasErrors() ? [] : $exercisesResult->getData());
    $activeExercises = count($exercises);
    $activePlans = count($plans);

    // Get latest pain level from most recent assessment
    $latestPainLevel = null;
    if (!empty($assessments) && isset($assessments[0]['pain_level'])) {
        $latestPainLevel = intval($assessments[0]['pain_level']);
    }

    // Check for overdue follow-ups (plans active > 8 weeks)
    $overdueCount = 0;
    $currentDate = new DateTime();
    foreach ($plans as $plan) {
        if (!empty($plan['start_date'])) {
            $startDate = new DateTime($plan['start_date']);
            $weeksDiff = $currentDate->diff($startDate)->days / 7;
            $expectedDuration = !empty($plan['estimated_duration_weeks']) ? intval($plan['estimated_duration_weeks']) : 8;
            if ($weeksDiff > $expectedDuration) {
                $overdueCount++;
            }
        }
    }

    ob_start();
    ?>
    <div class="vietnamese-pt-widget card mb-3">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fa fa-heartbeat"></i> <?php echo xlt('Vietnamese Physiotherapy'); ?>
            </h5>
        </div>
        <div class="card-body">
            <!-- Quick Stats Section -->
            <div class="pt-quick-stats mb-4">
                <div class="row text-center">
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="stat-box border rounded p-2 bg-light">
                            <div class="stat-number text-primary" style="font-size: 24pt; font-weight: bold;">
                                <?php echo text($totalAssessments); ?>
                            </div>
                            <div class="stat-label text-muted" style="font-size: 9pt;">
                                <?php echo xlt('Total Assessments'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="stat-box border rounded p-2 bg-light">
                            <div class="stat-number text-success" style="font-size: 24pt; font-weight: bold;">
                                <?php echo text($activeExercises); ?>
                            </div>
                            <div class="stat-label text-muted" style="font-size: 9pt;">
                                <?php echo xlt('Active Exercises'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="stat-box border rounded p-2 bg-light">
                            <div class="stat-number text-info" style="font-size: 24pt; font-weight: bold;">
                                <?php echo text($activePlans); ?>
                            </div>
                            <div class="stat-label text-muted" style="font-size: 9pt;">
                                <?php echo xlt('Active Plans'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="stat-box border rounded p-2 <?php echo $overdueCount > 0 ? 'bg-warning' : 'bg-light'; ?>">
                            <div class="stat-number <?php echo $overdueCount > 0 ? 'text-danger' : 'text-secondary'; ?>" style="font-size: 24pt; font-weight: bold;">
                                <?php echo text($overdueCount); ?>
                            </div>
                            <div class="stat-label text-muted" style="font-size: 9pt;">
                                <?php echo xlt('Overdue Follow-ups'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Latest Pain Level Indicator -->
                <?php if ($latestPainLevel !== null): ?>
                <div class="alert alert-<?php echo $latestPainLevel <= 3 ? 'success' : ($latestPainLevel <= 6 ? 'warning' : 'danger'); ?> mt-3 mb-3 py-2">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <i class="fa fa-heartbeat"></i> <strong><?php echo xlt('Latest Pain Level'); ?>:</strong>
                        </div>
                        <div class="col-4 text-right">
                            <span style="font-size: 18pt; font-weight: bold;"><?php echo text($latestPainLevel); ?>/10</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <!-- Recent Assessments -->
            <div class="mb-3">
                <h6 class="border-bottom pb-2">
                    <i class="fa fa-stethoscope"></i> <?php echo xlt('Recent Assessments'); ?>
                    <a href="#" class="btn btn-sm btn-primary float-right" onclick="addNewPTForm('vietnamese_pt_assessment')">
                        <i class="fa fa-plus"></i> <?php echo xlt('New'); ?>
                    </a>
                </h6>
                <?php if (empty($assessments)): ?>
                    <p class="text-muted"><?php echo xlt('No assessments recorded'); ?></p>
                <?php else: ?>
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th><?php echo xlt('Date'); ?></th>
                                <th><?php echo xlt('Chief Complaint'); ?></th>
                                <th><?php echo xlt('Pain'); ?></th>
                                <th><?php echo xlt('Status'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assessments as $assessment): ?>
                            <tr>
                                <td><?php echo text(date('Y-m-d', strtotime($assessment['assessment_date'] ?? ''))); ?></td>
                                <td>
                                    <?php
                                    $complaint = $assessment['chief_complaint_vi'] ?? $assessment['chief_complaint_en'] ?? '';
                                    echo text(substr($complaint, 0, 50) . (strlen($complaint) > 50 ? '...' : ''));
                                    ?>
                                </td>
                                <td>
                                    <?php if (isset($assessment['pain_level'])): ?>
                                        <span class="badge badge-<?php echo $assessment['pain_level'] <= 3 ? 'success' : ($assessment['pain_level'] <= 6 ? 'warning' : 'danger'); ?>">
                                            <?php echo text($assessment['pain_level']); ?>/10
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo text($assessment['status'] ?? ''); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Active Exercises -->
            <div class="mb-3">
                <h6 class="border-bottom pb-2">
                    <i class="fa fa-bicycle"></i> <?php echo xlt('Active Exercise Prescriptions'); ?>
                    <a href="#" class="btn btn-sm btn-success float-right" onclick="addNewPTForm('vietnamese_pt_exercise')">
                        <i class="fa fa-plus"></i> <?php echo xlt('New'); ?>
                    </a>
                </h6>
                <?php if (empty($exercises)): ?>
                    <p class="text-muted"><?php echo xlt('No active exercises'); ?></p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($exercises as $exercise): ?>
                        <li class="list-group-item">
                            <strong><?php echo text($exercise['exercise_name_vi'] ?? $exercise['exercise_name'] ?? ''); ?></strong>
                            <br>
                            <small class="text-muted">
                                <?php echo text($exercise['sets_prescribed'] ?? ''); ?> sets
                                <?php if (!empty($exercise['reps_prescribed'])): ?>
                                    × <?php echo text($exercise['reps_prescribed']); ?> reps
                                <?php endif; ?>
                                - <?php echo text($exercise['frequency_per_week'] ?? ''); ?>x/week
                                <span class="badge badge-info"><?php echo text($exercise['intensity_level'] ?? ''); ?></span>
                            </small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Active Treatment Plans -->
            <div class="mb-0">
                <h6 class="border-bottom pb-2">
                    <i class="fa fa-clipboard"></i> <?php echo xlt('Active Treatment Plans'); ?>
                    <a href="#" class="btn btn-sm btn-info float-right" onclick="addNewPTForm('vietnamese_pt_treatment_plan')">
                        <i class="fa fa-plus"></i> <?php echo xlt('New'); ?>
                    </a>
                </h6>
                <?php if (empty($plans)): ?>
                    <p class="text-muted"><?php echo xlt('No active treatment plans'); ?></p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($plans as $plan): ?>
                        <li class="list-group-item">
                            <strong><?php echo text($plan['plan_name'] ?? ''); ?></strong>
                            <span class="badge badge-<?php echo $plan['status'] == 'active' ? 'success' : 'secondary'; ?> float-right">
                                <?php echo text($plan['status'] ?? ''); ?>
                            </span>
                            <br>
                            <small class="text-muted">
                                <?php echo xlt('Started'); ?>: <?php echo text($plan['start_date'] ?? ''); ?>
                                <?php if (!empty($plan['estimated_duration_weeks'])): ?>
                                    - <?php echo text($plan['estimated_duration_weeks']); ?> <?php echo xlt('weeks'); ?>
                                <?php endif; ?>
                            </small>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function addNewPTForm(formDirectory) {
            // Navigate to form within encounter
            if (typeof parent.left_nav !== 'undefined') {
                var encounterFrame = parent.left_nav.getEncounterFrame();
                if (encounterFrame) {
                    encounterFrame.location.href = '../../interface/forms/' + formDirectory + '/new.php';
                }
            }
            return false;
        }
    </script>
    <?php

    return ob_get_clean();
}

/**
 * Hook function to integrate widget into patient summary
 * This can be called from patient summary customization
 */
function vietnamese_pt_widget_hook($patient_id)
{
    echo renderVietnamesePTWidget($patient_id);
}

/* AI-ENHANCED CODE - END */