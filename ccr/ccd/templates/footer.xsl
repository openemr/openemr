<?xml version="1.0" encoding="UTF-8"?>
<!--
Conversion of CCR to Level 3 CCD

Orginal Author:   	Ken Miller
Solventus LLC
ken.miller@solventus.coms

Contributors:
Richard Braman, EHR Doctors, Inc rbraman@ehrdoctors.com
George Lilly (WorldVistA glilly@glilly.net)
xxxx - Oroville Hospital

Date: 	2010-05-5
Version: 	0.1

License :

        This program is free software: you can redistribute it and/or modify
        it under the terms of the GNU General Public License as published by
        the Free Software Foundation, either version 3 of the License, or
        (at your option) any later version.

        This program is distributed in the hope that it will be useful,
        but WITHOUT ANY WARRANTY; without even the implied warranty of
        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
        GNU General Public License for more details.

        You should have received a copy of the GNU General Public License
        along with this program.  If not, see http://www.gnu.org/licenses.

-->
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	<!-- HTML Footer for CCR.XSL -->
	<xsl:template name="footer">
		<br/>
		<hr/>
		<table cellspacing="3" bgcolor="#006666">
			<tbody>
				<tr>
					<th>
						<font size="2" color="#FFFF99">
	This stylesheet is provided by the American Academy of Family Physicians and the CCR Acceleration Task Force
</font>
					</th>
				</tr>
				<tr>
					<td/>
				</tr>
				<tr>
					<td>
						<font size="3" color="#FFFF99"><strong>Powered by the <a style="color:white;" href="http://www.astm.org/cgi-bin/SoftCart.exe/DATABASE.CART/REDLINE_PAGES/E2369.htm?E+mystore">ASTM E2369-05 Specification for the Continuity of Care Record (CCR)</a> which includes:</strong></font>
					</td>
				</tr>
				<tr>
					<td>
				<table cellpadding="2" cellspacing="2" width="100%">
					<tbody>
						<tr>
							<td><font size="2" color="#FFFF99">Advance Directives</font></td>
							<td><font size="2" color="#FFFF99">Alerts / Allergies</font></td>
							<td><font size="2" color="#FFFF99">Encounters</font></td>
							<td><font size="2" color="#FFFF99">Family History</font></td>
							<td><font size="2" color="#FFFF99">Functional Status</font></td>
						</tr>
						<tr>
							<td><font size="2" color="#FFFF99">Health Care Providers</font></td>
							<td><font size="2" color="#FFFF99">Immunizations</font></td>
							<td><font size="2" color="#FFFF99">Insurance</font></td>
							<td><font size="2" color="#FFFF99">Medical Equipment</font></td>
							<td><font size="2" color="#FFFF99">Medications</font></td>
						</tr>
						<tr>
							<td><font size="2" color="#FFFF99">Plan Of Care</font></td>
							<td><font size="2" color="#FFFF99">Problems</font></td>
							<td><font size="2" color="#FFFF99">Procedures</font></td>
							<td><font size="2" color="#FFFF99">Results</font></td>
							<td><font size="2" color="#FFFF99">Social History</font></td>
						</tr>
						<tr>
							<td><font size="2" color="#FFFF99">Support Providers</font></td>
							<td><font size="2" color="#FFFF99">Vital Signs</font></td>
						</tr>
					</tbody>
				</table>
			
					</td>
				</tr>
			</tbody>
		</table>		
	</xsl:template>
</xsl:stylesheet>
