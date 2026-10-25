<?php
/**
 * Plan section template for dermatology PDF reports
 */

// Variables expected to be available:
// $planNumber - The plan number
// $findingLocation - The finding location
// $diagnosisDescription - The diagnosis description
// $icdAlt - The ICD alternate code
// $diagnosisAlt - The diagnosis alternate
// $severity - The severity
// $procedurePlans - The procedure plans
// $planComplete - The complete plan
// $planDescription - The plan description
// $biopsyLocationTitle - The biopsy location title
// $biopsyAreas - The biopsy areas
// $ruleOutText - The rule out text
// $biopsyMethods - The biopsy methods as an array
?>
<div>
    <h4> Plan # <?php echo $planNumber; ?> : </h4>
    <p>
        <?php if (!empty($findingLocation)): ?>
            <?php echo $findingLocation; ?><br>
        <?php endif; ?>
        
        <?php if (!empty($diagnosisDescription)): ?>
            <?php echo ucfirst($diagnosisDescription); ?><br>
        <?php endif; ?>
        
        <?php if (!empty($icdAlt)): ?>
            <?php echo ucfirst($icdAlt); ?><br>
        <?php endif; ?>
        
        <?php if (!empty($diagnosisAlt)): ?>
            <?php echo ucfirst($diagnosisAlt); ?><br>
        <?php endif; ?>
        
        <?php if (!empty($severity)): ?>
            <?php echo $severity; ?><br>
        <?php endif; ?>
        
        <?php if (!empty($procedurePlans)): ?>
            <b><?php echo $procedurePlans; ?></b><br>
            <?php if (!empty($planComplete)): ?>
                <?php echo nl2br($planComplete); ?><br>
            <?php endif; ?>
        <?php elseif (!empty($planComplete)): ?>
            <?php echo nl2br($planComplete); ?><br>
        <?php endif; ?>
        
        <?php if (!empty($planDescription)): ?>
            <?php echo $planDescription; ?><br>
        <?php endif; ?>
        
        <?php if (!empty($biopsyLocationTitle)): ?>
            <b><?php echo $biopsyLocationTitle; ?></b><br>
        <?php endif; ?>
        
        <?php if (!empty($biopsyAreas)): ?>
            <b>Biopsy Location:</b> <?php echo $biopsyAreas; ?><br>
        <?php endif; ?>
        
        <?php if (!empty($ruleOutText)): ?>
            <b>Rule Out:</b> <?php echo $ruleOutText; ?><br>
        <?php endif; ?>
        
        <?php if (!empty($biopsyMethods)): ?>
            <b>Biopsy Method:</b> <?php echo implode(', ', $biopsyMethods); ?><br>
        <?php endif; ?>
    </p>
</div>
<br>
