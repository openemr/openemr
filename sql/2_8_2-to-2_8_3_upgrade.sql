ALTER TABLE form_vitals
  MODIFY `weight` FLOAT(5,2) default 0,
  MODIFY `height` FLOAT(5,2) default 0,
  MODIFY `BMI`    FLOAT(4,1) default 0;
