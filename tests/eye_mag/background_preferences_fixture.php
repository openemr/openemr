<?php
/** Actual drawing renderer and runtime, backed only by the synthetic HTTP site. */
require __DIR__ . '/interface/globals.php';
require __DIR__ . '/interface/forms/eye_mag/php/drawing_backgrounds.php';
class_alias(\OpenEMR\Common\Session\SessionWrapperFactory::class, 'SessionWrapperFactory');
class_alias(\OpenEMR\Core\OEGlobalsBag::class, 'OEGlobalsBag');
function eye_mag_ensure_drawing_overlays_table() {}
function xlj($value) { return json_encode($value); }
$source = file_get_contents(__DIR__ . '/interface/forms/eye_mag/php/eye_mag_functions.php');
foreach (['canvas_select', 'display_draw_section'] as $function) {
    $start = strpos($source, 'function ' . $function . '(');
    $end = strpos($source, "\n/**", $start);
    eval(substr($source, $start, $end - $start));
}
$form_folder = 'eye_mag';$NEURO_DRAWING_JSON = '[]';$id = 1;$documents = [];
?><!doctype html><html><head><meta charset="utf-8"><base href="/interface/patient_file/encounter/"><title>Eye background fixture</title>
<link rel="stylesheet" href="/interface/forms/eye_mag/css/style.css">
<style>body{font:14px Arial;margin:24px;background:#f5f7f8}#Draw_RETINA{display:block;position:relative;max-width:100%}.canvas{float:none!important}.eye-drawing-stage{position:relative}.eye-konva-stage{position:absolute;inset:0}.eye-drawing-command-row{display:block!important}.closeButton,.closeButton_2,.closeButton_3{display:none}</style>
<script src="/interface/forms/eye_mag/js/jquery-1-10-2/jquery.min.js"></script><script src="/fixture-konva.js"></script>
</head><body><h2>Retinal drawing</h2><?php display_draw_section('RETINA', 20, 10); ?>
<div style="display:none"><?php foreach (['HPI','PMH','EXT','ANTSEG','SDRETINA','NEURO','IMPPLAN'] as $hiddenZone) display_draw_section($hiddenZone, 20, 10); ?></div>
<script>window.fixtureSaves=[];window.fixtureRestoreCount=0;window.restoreSession=function(){window.fixtureRestoreCount++};
window.submit_canvas=function(zone,force){var overlay=window.eyeDrawingOverlays?.[zone],source=document.getElementById('myCanvas_'+zone);function record(canvas){window.fixtureSaves.push({zone:zone,key:$('#current_background_'+zone).val(),json:overlay?.getJSON(),image:canvas.toDataURL()});clearEyeCanvasDirty(zone);overlay?.markSaved();$('#myCanvas_'+zone).closest('.eye-drawing-stage').attr('data-has-saved-drawing','1');}return overlay?.exportCompositeCanvas?overlay.exportCompositeCanvas(source).then(record):record(source);};</script>
<script src="/interface/forms/eye_mag/js/canvasdraw.js"></script>
<script><?php
$js = file_get_contents(__DIR__ . '/interface/forms/eye_mag/js/eye_base.php');
$start = strpos($js, '// Provider defaults are separate');
$end = strpos($js, 'function persist_canvas(', $start);
eval('?>' . substr($js, $start, $end - $start));
?></script><script src="/interface/forms/eye_mag/js/konva_overlay.js"></script>
<script src="/interface/forms/eye_mag/js/drawing_library.js"></script>
</body></html>
