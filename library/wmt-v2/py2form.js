function SetExamNormal()
{
	// There is really no way to loop this any more
  document.py1_form.elements['py1_ge_gen_norm'].checked=true;
  document.py1_form.elements['py1_ge_gen_dev'].checked=true;
  document.py1_form.elements['py1_ge_gen_groom'].checked=true;
  document.py1_form.elements['py1_ge_gen_dis'].checked=true;
  document.py1_form.elements['py1_ge_gen_jaun'].selectedIndex='2';
  document.py1_form.elements['py1_ge_gen_waste'].selectedIndex='2';
  document.py1_form.elements['py1_ge_gen_sleep'].selectedIndex='1';
  document.py1_form.elements['py1_ge_neu_ao'].selectedIndex='3';
  document.py1_form.elements['py1_ge_neu_cn'].selectedIndex='1';
  document.py1_form.elements['py1_ge_psych_judge'].checked=true;
  document.py1_form.elements['py1_ge_psych_orient'].checked=true;
  document.py1_form.elements['py1_ge_psych_memory'].checked=true;
  document.py1_form.elements['py1_ge_psych_mood'].checked=true;
}

