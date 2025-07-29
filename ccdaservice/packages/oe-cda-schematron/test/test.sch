<?xml version="1.0" encoding="UTF-8"?>
<sch:schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:sch="http://purl.oclc.org/dsdl/schematron">
	<sch:ns prefix="dh" uri="urn:dh" />
	<sch:phase id="errors">
		<sch:active pattern="A" />
		<sch:active pattern="B" />
		<sch:active pattern="C" />
		<sch:active pattern="E" />
	</sch:phase>
	<sch:phase id="warnings">
		<sch:active pattern="D" />
	</sch:phase>
	<sch:pattern id="A">
		<sch:rule id="A-rule" context="dh:test[@root='A']">
			<sch:assert id="A-assert" test="count(dh:element)=1">Should only have one element</sch:assert>
		</sch:rule>
	</sch:pattern>
	<sch:pattern id="B">
		<sch:rule id="B-rule-1" context="dh:test[@root]">
			<sch:assert id="B-assert-2" test="count(dh:element)=1">Each section should have 1 element</sch:assert>
		</sch:rule>
		<sch:rule id="B-rule-2">
			<sch:assert id="B-assert-1" test="count(//dh:element)=18">Should have 18 elements total element</sch:assert>
		</sch:rule>
	</sch:pattern>
	<sch:pattern id="C">
		<sch:rule id="C-rule-1">
			<sch:assert id="C-assert-1" test="//dh:element[@attribute]">Each element should have the attribute attribute</sch:assert>
		</sch:rule>
		<sch:rule id="C-rule-2" context="dh:test[@root]">
			<sch:assert id="C-assert-2" test="dh:element[@attribute]">Each element should have the attribute attribute</sch:assert>
		</sch:rule>
	</sch:pattern>
	<sch:pattern id="D">
		<sch:rule id="D-rule-1">
			<sch:assert id="D-assert-1" test="//dh:element[count(dh:child)=1]">Each element should have the attribute attribute</sch:assert>
		</sch:rule>
		<sch:rule id="D-rule-2" context="dh:test[@root]">
			<sch:assert id="D-assert-2" test="dh:element[count(dh:child)=1]">Each element should have the attribute attribute</sch:assert>
		</sch:rule>
	</sch:pattern>
	<sch:pattern id="E">
		<sch:rule id="E-rule-1">
			<sch:assert id="E-assert-1" test="not (dh:element[count(dh:child)=1])">This is an invalid test</sch:assert>
		</sch:rule>
	</sch:pattern>
</sch:schema>