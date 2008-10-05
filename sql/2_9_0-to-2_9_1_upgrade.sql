ALTER TABLE form_misc_billing_options
  ADD replacement_claim tinyint(1) DEFAULT 0;

ALTER TABLE insurance_data
  ADD accept_assignment varchar(5) NOT NULL DEFAULT 'TRUE';
