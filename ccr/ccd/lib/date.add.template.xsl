<?xml version="1.0"?>

<!-- This is a public domain script released from http://exslt.org/ -->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:date="http://exslt.org/dates-and-times"
                extension-element-prefixes="date">
 
<date:month-lengths>
   <date:month>31</date:month>
   <date:month>28</date:month>
   <date:month>31</date:month>
   <date:month>30</date:month>
   <date:month>31</date:month>
   <date:month>30</date:month>
   <date:month>31</date:month>
   <date:month>31</date:month>
   <date:month>30</date:month>
   <date:month>31</date:month>
   <date:month>30</date:month>
   <date:month>31</date:month>
</date:month-lengths>

<xsl:template name="date:add">
	<xsl:param name="date-time" />
   <xsl:param name="duration" />
   <xsl:variable name="dt-neg" select="starts-with($date-time, '-')" />
   <xsl:variable name="dt-no-neg">
      <xsl:choose>
         <xsl:when test="$dt-neg or starts-with($date-time, '+')">
            <xsl:value-of select="substring($date-time, 2)" />
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="$date-time" />
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:variable name="dt-no-neg-length" select="string-length($dt-no-neg)" />
   <xsl:variable name="timezone">
      <xsl:choose>
         <xsl:when test="substring($dt-no-neg, $dt-no-neg-length) = 'Z'">Z</xsl:when>
         <xsl:otherwise>
            <xsl:variable name="tz" select="substring($dt-no-neg, $dt-no-neg-length - 5)" />
            <xsl:if test="(substring($tz, 1, 1) = '-' or 
                           substring($tz, 1, 1) = '+') and
                          substring($tz, 4, 1) = ':'">
               <xsl:value-of select="$tz" />
            </xsl:if>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:variable name="new-dt">
      <xsl:if test="not(string($timezone)) or
                    $timezone = 'Z' or 
                    (substring($timezone, 2, 2) &lt;= 23 and
                     substring($timezone, 5, 2) &lt;= 59)">
         <xsl:variable name="dt" select="substring($dt-no-neg, 1, $dt-no-neg-length - string-length($timezone))" />
         <xsl:variable name="dt-length" select="string-length($dt)" />
         <xsl:variable name="du-neg" select="starts-with($duration, '-')" />
         <xsl:variable name="du">
            <xsl:choose>
               <xsl:when test="$du-neg"><xsl:value-of select="substring($duration, 2)" /></xsl:when>
               <xsl:otherwise><xsl:value-of select="$duration" /></xsl:otherwise>
            </xsl:choose>
         </xsl:variable>
         <xsl:if test="starts-with($du, 'P') and
                       not(translate($du, '0123456789PYMDTHS.', ''))">
            <xsl:variable name="du-date">
               <xsl:choose>
                  <xsl:when test="contains($du, 'T')"><xsl:value-of select="substring-before(substring($du, 2), 'T')" /></xsl:when>
                  <xsl:otherwise><xsl:value-of select="substring($du, 2)" /></xsl:otherwise>
               </xsl:choose>
            </xsl:variable>
            <xsl:variable name="du-time">
               <xsl:if test="contains($du, 'T')"><xsl:value-of select="substring-after($du, 'T')" /></xsl:if>
            </xsl:variable>
            <xsl:if test="(not($du-date) or
                           (not(translate($du-date, '0123456789YMD', '')) and
                            not(substring-after($du-date, 'D')) and
                            (contains($du-date, 'D') or 
                             (not(substring-after($du-date, 'M')) and
                              (contains($du-date, 'M') or
                               not(substring-after($du-date, 'Y'))))))) and
                          (not($du-time) or
                           (not(translate($du-time, '0123456789HMS.', '')) and
                            not(substring-after($du-time, 'S')) and
                            (contains($du-time, 'S') or
                             not(substring-after($du-time, 'M')) and
                             (contains($du-time, 'M') or
                              not(substring-after($du-time, 'Y'))))))">
               <xsl:variable name="duy-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-date, 'Y')"><xsl:value-of select="substring-before($du-date, 'Y')" /></xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="dum-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-date, 'M')">
                        <xsl:choose>
                           <xsl:when test="contains($du-date, 'Y')"><xsl:value-of select="substring-before(substring-after($du-date, 'Y'), 'M')" /></xsl:when>
                           <xsl:otherwise><xsl:value-of select="substring-before($du-date, 'M')" /></xsl:otherwise>
                        </xsl:choose>
                     </xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="dud-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-date, 'D')">
                        <xsl:choose>
                           <xsl:when test="contains($du-date, 'M')"><xsl:value-of select="substring-before(substring-after($du-date, 'M'), 'D')" /></xsl:when>
                           <xsl:when test="contains($du-date, 'Y')"><xsl:value-of select="substring-before(substring-after($du-date, 'Y'), 'D')" /></xsl:when>
                           <xsl:otherwise><xsl:value-of select="substring-before($du-date, 'D')" /></xsl:otherwise>
                        </xsl:choose>
                     </xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="duh-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-time, 'H')"><xsl:value-of select="substring-before($du-time, 'H')" /></xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="dumin-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-time, 'M')">
                        <xsl:choose>
                           <xsl:when test="contains($du-time, 'H')"><xsl:value-of select="substring-before(substring-after($du-time, 'H'), 'M')" /></xsl:when>
                           <xsl:otherwise><xsl:value-of select="substring-before($du-time, 'M')" /></xsl:otherwise>
                        </xsl:choose>
                     </xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="dus-str">
                  <xsl:choose>
                     <xsl:when test="contains($du-time, 'S')">
                        <xsl:choose>
                           <xsl:when test="contains($du-time, 'M')"><xsl:value-of select="substring-before(substring-after($du-time, 'M'), 'S')" /></xsl:when>
                           <xsl:when test="contains($du-time, 'H')"><xsl:value-of select="substring-before(substring-after($du-time, 'H'), 'S')" /></xsl:when>
                           <xsl:otherwise><xsl:value-of select="substring-before($du-time, 'S')" /></xsl:otherwise>
                        </xsl:choose>
                     </xsl:when>
                     <xsl:otherwise>0</xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:variable name="mult" select="($du-neg * -2) + 1" />
               <xsl:variable name="duy" select="$duy-str * $mult" />
               <xsl:variable name="dum" select="$dum-str * $mult" />
               <xsl:variable name="dud" select="$dud-str * $mult" />
               <xsl:variable name="duh" select="$duh-str * $mult" />
               <xsl:variable name="dumin" select="$dumin-str * $mult" />
               <xsl:variable name="dus" select="$dus-str * $mult" />

               <xsl:variable name="year" select="substring($dt, 1, 4) * (($dt-neg * -2) + 1)" />
               <xsl:choose>
                  <xsl:when test="$year and
                                  string($duy) = 'NaN' or 
                                  string($dum) = 'NaN' or 
                                  string($dud) = 'NaN' or 
                                  string($duh) = 'NaN' or 
                                  string($dumin) = 'NaN' or 
                                  string($dus) = 'NaN'" />
                  <xsl:when test="$dt-length > 4 or
                                  $dum or $dud or $duh or $dumin or $dus">
                     <xsl:variable name="month">
                        <xsl:choose>
                           <xsl:when test="$dt-length > 4">
                              <xsl:if test="substring($dt, 5, 1) = '-'">
                                 <xsl:value-of select="substring($dt, 6, 2)" />
                              </xsl:if>
                           </xsl:when>
                           <xsl:otherwise>1</xsl:otherwise>
                        </xsl:choose>
                     </xsl:variable>
                     <xsl:choose>
                        <xsl:when test="not($month) or $month > 12" />
                        <xsl:when test="$dt-length > 7 or
                                        $dud or $duh or $dumin or $dus">
                           <xsl:variable name="day">
                              <xsl:choose>
                                 <xsl:when test="$dt-length > 7">
                                    <xsl:if test="substring($dt, 8, 1) = '-'">
                                       <xsl:value-of select="substring($dt, 9, 2)" />
                                    </xsl:if>
                                 </xsl:when>
                                 <xsl:otherwise>1</xsl:otherwise>
                              </xsl:choose>
                           </xsl:variable>
                           <xsl:choose>
                              <xsl:when test="not($day) or $day > 31" />
                              <xsl:when test="$dt-length > 10 or
                                              $duh or $dumin or $dus">
                                 <xsl:if test="$dt-length = 10 or
                                               (substring($dt, 11, 1) = 'T' and
                                                substring($dt, 14, 1) = ':' and
                                                substring($dt, 17, 1) = ':')">
                                    <xsl:variable name="hour">
                                       <xsl:choose>
                                          <xsl:when test="$dt-length > 10"><xsl:value-of select="substring($dt, 12, 2)" /></xsl:when>
                                          <xsl:otherwise>0</xsl:otherwise>
                                       </xsl:choose>
                                    </xsl:variable>
                                    <xsl:variable name="minute">
                                       <xsl:choose>
                                          <xsl:when test="$dt-length > 10"><xsl:value-of select="substring($dt, 15, 2)" /></xsl:when>
                                          <xsl:otherwise>0</xsl:otherwise>
                                       </xsl:choose>
                                    </xsl:variable>
                                    <xsl:variable name="second">
                                       <xsl:choose>
                                          <xsl:when test="$dt-length > 10"><xsl:value-of select="substring($dt, 18)" /></xsl:when>
                                          <xsl:otherwise>0</xsl:otherwise>
                                       </xsl:choose>
                                    </xsl:variable>
                                    <xsl:if test="$hour &lt;= 23 and $minute &lt;= 59 and $second &lt;= 60">
                                       <xsl:variable name="new-second" select="$second + $dus" />
                                       <xsl:variable name="new-minute" select="$minute + $dumin + floor($new-second div 60)" />
                                       <xsl:variable name="new-hour" select="$hour + $duh + floor($new-minute div 60)" />
                                       <xsl:variable name="new-month" select="$month + $dum" />
                                       <xsl:call-template name="date:_add-days">
                                          <xsl:with-param name="year" select="$year + $duy + floor(($new-month - 1) div 12)" />
                                          <xsl:with-param name="month">
                                             <xsl:variable name="m">
                                                <xsl:choose>
                                                   <xsl:when test="$new-month &lt; 1"><xsl:value-of select="$new-month + 12" /></xsl:when>
                                                   <xsl:otherwise><xsl:value-of select="$new-month" /></xsl:otherwise>
                                                </xsl:choose>
                                             </xsl:variable>
                                             <xsl:choose>
                                                <xsl:when test="$m mod 12">
                                                   <xsl:value-of select="format-number($m mod 12, '00')" />
                                                </xsl:when>
                                                <xsl:otherwise>12</xsl:otherwise>
                                             </xsl:choose>
                                          </xsl:with-param>
                                          <xsl:with-param name="day" select="$day" />
                                          <xsl:with-param name="days" select="$dud + floor($new-hour div 24)" />
                                       </xsl:call-template>
                                       <xsl:text>T</xsl:text>
                                       <xsl:value-of select="format-number(($new-hour + 24) mod 24, '00')" />
                                       <xsl:text>:</xsl:text>
                                       <xsl:value-of select="format-number($new-minute mod 60, '00')" />
                                       <xsl:text>:</xsl:text>
                                       <xsl:if test="$new-second mod 60 &lt; 10">0</xsl:if>
                                       <xsl:value-of select="$new-second mod 60" />
                                       <xsl:value-of select="$timezone" />
                                    </xsl:if>
                                 </xsl:if>
                              </xsl:when>
                              <xsl:otherwise>
                                 <xsl:variable name="new-month" select="$month + $dum" />
                                 <xsl:call-template name="date:_add-days">
                                    <xsl:with-param name="year" select="$year + $duy + floor(($new-month - 1) div 12)" />
                                    <xsl:with-param name="month">
                                       <xsl:variable name="m">
                                          <xsl:choose>
                                             <xsl:when test="$new-month &lt; 1"><xsl:value-of select="$new-month + 12" /></xsl:when>
                                             <xsl:otherwise><xsl:value-of select="$new-month" /></xsl:otherwise>
                                          </xsl:choose>
                                       </xsl:variable>
                                       <xsl:choose>
                                          <xsl:when test="$m mod 12">
                                             <xsl:value-of select="format-number($m mod 12, '00')" />
                                          </xsl:when>
                                          <xsl:otherwise>12</xsl:otherwise>
                                       </xsl:choose>
                                    </xsl:with-param>
                                    <xsl:with-param name="day" select="$day" />
                                    <xsl:with-param name="days" select="$dud" />
                                 </xsl:call-template>
                                 <xsl:value-of select="$timezone" />
                              </xsl:otherwise>
                           </xsl:choose>
                        </xsl:when>
                        <xsl:otherwise>
                           <xsl:variable name="new-month" select="$month + $dum" />
                           <xsl:value-of select="format-number($year + $duy + floor(($new-month - 1) div 12), '0000')" />
                           <xsl:text>-</xsl:text>
                           <xsl:variable name="m">
                              <xsl:choose>
                                 <xsl:when test="$new-month &lt; 1"><xsl:value-of select="$new-month + 12" /></xsl:when>
                                 <xsl:otherwise><xsl:value-of select="$new-month" /></xsl:otherwise>
                              </xsl:choose>
                           </xsl:variable>
                           <xsl:choose>
                              <xsl:when test="$m mod 12">
                                 <xsl:value-of select="format-number($m mod 12, '00')" />
                              </xsl:when>
                              <xsl:otherwise>12</xsl:otherwise>
                           </xsl:choose>
                           <xsl:value-of select="$timezone" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:otherwise>
                     <xsl:value-of select="format-number($year + $duy, '0000')" />
                     <xsl:value-of select="$timezone" />
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:if>
         </xsl:if>
      </xsl:if>
   </xsl:variable>
   <xsl:choose>
     <xsl:when test="string-length($date-time) > 10">
       <xsl:value-of select="$new-dt" />
     </xsl:when>
     <xsl:otherwise>
       <xsl:value-of select="substring($new-dt, 1, string-length($date-time))" />
     </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="date:_add-days">
   <xsl:param name="year" />
   <xsl:param name="month" />
   <xsl:param name="day" />
   <xsl:param name="days" />
   <xsl:param name="new-day" select="'NaN'" />
   <xsl:variable name="leap" select="(not($year mod 4) and $year mod 100) or not($year mod 400)" />
   <xsl:variable name="month-days" select="document('')/*/date:month-lengths/date:month" />
   <xsl:variable name="days-in-month">
      <xsl:choose>
         <xsl:when test="$month = 2 and $leap">
            <xsl:value-of select="$month-days[number($month)] + 1" />
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="$month-days[number($month)]" />
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:choose>
      <xsl:when test="$new-day = 'NaN'">
         <xsl:call-template name="date:_add-days">
            <xsl:with-param name="year" select="$year" />
            <xsl:with-param name="month" select="$month" />
            <xsl:with-param name="new-day">
               <xsl:choose>
                  <xsl:when test="$day > $days-in-month">
                     <xsl:value-of select="$days-in-month + $days" />
                  </xsl:when>
                  <xsl:otherwise><xsl:value-of select="$day + $days" /></xsl:otherwise>
               </xsl:choose>
            </xsl:with-param>
         </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
         <xsl:choose>
            <xsl:when test="$new-day &lt; 1">
               <xsl:call-template name="date:_add-days">
                  <xsl:with-param name="year" select="$year - ($month = 1)" />
                  <xsl:with-param name="month">
                     <xsl:choose>
                        <xsl:when test="$month = 1">12</xsl:when>
                        <xsl:otherwise><xsl:value-of select="$month - 1" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:with-param>
                  <xsl:with-param name="new-day">
                     <xsl:variable name="days-in-new-month">
                        <xsl:choose>
                           <xsl:when test="$leap and $month = 3">29</xsl:when>
                           <xsl:when test="$month = 1">31</xsl:when>
                           <xsl:otherwise>
                              <xsl:value-of select="$month-days[$month - 1]" />
                           </xsl:otherwise>
                        </xsl:choose>
                     </xsl:variable>                     
                     <xsl:value-of select="$new-day + $days-in-new-month" />
                  </xsl:with-param>
               </xsl:call-template>
            </xsl:when>
            <xsl:when test="$new-day > $days-in-month">
               <xsl:call-template name="date:_add-days">
                  <xsl:with-param name="year" select="$year + ($month = 12)" />
                  <xsl:with-param name="month">
                     <xsl:choose>
                        <xsl:when test="$month = 12">1</xsl:when>
                        <xsl:otherwise><xsl:value-of select="$month + 1" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:with-param>
                  <xsl:with-param name="new-day" select="$new-day - $days-in-month" />
               </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
               <xsl:value-of select="format-number($year, '0000')" />
               <xsl:text>-</xsl:text>
               <xsl:value-of select="format-number($month, '00')" />
               <xsl:text>-</xsl:text>
               <xsl:value-of select="format-number($new-day, '00')" />
            </xsl:otherwise>
         </xsl:choose>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

</xsl:stylesheet>
