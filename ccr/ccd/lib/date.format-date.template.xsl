<?xml version="1.0"?>

<!-- This is a public domain script released from http://exslt.org/ -->

<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:date="http://exslt.org/dates-and-times"
                xmlns:str="http://exslt.org/strings"
                extension-element-prefixes="date str">

<xsl:import href="./str.padding.template.xsl" />

<date:months>
   <date:month length="31" abbr="Jan">January</date:month>
   <date:month length="28" abbr="Feb">February</date:month>
   <date:month length="31" abbr="Mar">March</date:month>
   <date:month length="30" abbr="Apr">April</date:month>
   <date:month length="31" abbr="May">May</date:month>
   <date:month length="30" abbr="Jun">June</date:month>
   <date:month length="31" abbr="Jul">July</date:month>
   <date:month length="31" abbr="Aug">August</date:month>
   <date:month length="30" abbr="Sep">September</date:month>
   <date:month length="31" abbr="Oct">October</date:month>
   <date:month length="30" abbr="Nov">November</date:month>
   <date:month length="31" abbr="Dec">December</date:month>
</date:months>

<date:days>
   <date:day abbr="Sun">Sunday</date:day>
   <date:day abbr="Mon">Monday</date:day>
   <date:day abbr="Tue">Tuesday</date:day>
   <date:day abbr="Wed">Wednesday</date:day>
   <date:day abbr="Thu">Thursday</date:day>
   <date:day abbr="Fri">Friday</date:day>
   <date:day abbr="Sat">Saturday</date:day>
</date:days>

<xsl:template name="date:format-date">
	<xsl:param name="date-time" />
   <xsl:param name="pattern" />
   <xsl:variable name="formatted">
      <xsl:choose>
         <xsl:when test="starts-with($date-time, '---')">
            <xsl:call-template name="date:_format-date">
               <xsl:with-param name="year" select="'NaN'" />
               <xsl:with-param name="month" select="'NaN'" />
               <xsl:with-param name="day" select="number(substring($date-time, 4, 2))" />
               <xsl:with-param name="pattern" select="$pattern" />
            </xsl:call-template>
         </xsl:when>
         <xsl:when test="starts-with($date-time, '--')">
            <xsl:call-template name="date:_format-date">
               <xsl:with-param name="year" select="'NaN'" />
               <xsl:with-param name="month" select="number(substring($date-time, 3, 2))" />
               <xsl:with-param name="day" select="number(substring($date-time, 6, 2))" />
               <xsl:with-param name="pattern" select="$pattern" />
            </xsl:call-template>
         </xsl:when>
         <xsl:otherwise>
            <xsl:variable name="neg" select="starts-with($date-time, '-')" />
            <xsl:variable name="no-neg">
               <xsl:choose>
                  <xsl:when test="$neg or starts-with($date-time, '+')">
                     <xsl:value-of select="substring($date-time, 2)" />
                  </xsl:when>
                  <xsl:otherwise>
                     <xsl:value-of select="$date-time" />
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:variable>
            <xsl:variable name="no-neg-length" select="string-length($no-neg)" />
            <xsl:variable name="timezone">
               <xsl:choose>
                  <xsl:when test="substring($no-neg, $no-neg-length) = 'Z'">Z</xsl:when>
                  <xsl:otherwise>
                     <xsl:variable name="tz" select="substring($no-neg, $no-neg-length - 5)" />
                     <xsl:if test="(substring($tz, 1, 1) = '-' or 
                                    substring($tz, 1, 1) = '+') and
                                   substring($tz, 4, 1) = ':'">
                        <xsl:value-of select="$tz" />
                     </xsl:if>
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:variable>
            <xsl:if test="not(string($timezone)) or
                          $timezone = 'Z' or 
                          (substring($timezone, 2, 2) &lt;= 23 and
                           substring($timezone, 5, 2) &lt;= 59)">
               <xsl:variable name="dt" select="substring($no-neg, 1, $no-neg-length - string-length($timezone))" />
               <xsl:variable name="dt-length" select="string-length($dt)" />
               <xsl:choose>
                  <xsl:when test="substring($dt, 3, 1) = ':' and
                                  substring($dt, 6, 1) = ':'">
                     <xsl:variable name="hour" select="substring($dt, 1, 2)" />
                     <xsl:variable name="min" select="substring($dt, 4, 2)" />
                     <xsl:variable name="sec" select="substring($dt, 7)" />
                     <xsl:if test="$hour &lt;= 23 and
                                   $min &lt;= 59 and
                                   $sec &lt;= 60">
                        <xsl:call-template name="date:_format-date">
                           <xsl:with-param name="year" select="'NaN'" />
                           <xsl:with-param name="month" select="'NaN'" />
                           <xsl:with-param name="day" select="'NaN'" />
                           <xsl:with-param name="hour" select="$hour" />
                           <xsl:with-param name="minute" select="$min" />
                           <xsl:with-param name="second" select="$sec" />
                           <xsl:with-param name="timezone" select="$timezone" />
                           <xsl:with-param name="pattern" select="$pattern" />
                        </xsl:call-template>
                     </xsl:if>
                  </xsl:when>
                  <xsl:otherwise>
                     <xsl:variable name="year" select="substring($dt, 1, 4) * (($neg * -2) + 1)" />
                     <xsl:choose>
                        <xsl:when test="not(number($year))" />
                        <xsl:when test="$dt-length = 4">
                           <xsl:call-template name="date:_format-date">
                              <xsl:with-param name="year" select="$year" />
                              <xsl:with-param name="timezone" select="$timezone" />
                              <xsl:with-param name="pattern" select="$pattern" />
                           </xsl:call-template>
                        </xsl:when>
                        <xsl:when test="substring($dt, 5, 1) = '-'">
                           <xsl:variable name="month" select="substring($dt, 6, 2)" />
                           <xsl:choose>
                              <xsl:when test="not($month &lt;= 12)" />
                              <xsl:when test="$dt-length = 7">
                                 <xsl:call-template name="date:_format-date">
                                    <xsl:with-param name="year" select="$year" />
                                    <xsl:with-param name="month" select="$month" />
                                    <xsl:with-param name="timezone" select="$timezone" />
                                    <xsl:with-param name="pattern" select="$pattern" />
                                 </xsl:call-template>
                              </xsl:when>
                              <xsl:when test="substring($dt, 8, 1) = '-'">
                                 <xsl:variable name="day" select="substring($dt, 9, 2)" />
                                 <xsl:if test="$day &lt;= 31">
                                    <xsl:choose>
                                       <xsl:when test="$dt-length = 10">
                                          <xsl:call-template name="date:_format-date">
                                             <xsl:with-param name="year" select="$year" />
                                             <xsl:with-param name="month" select="$month" />
                                             <xsl:with-param name="day" select="$day" />
                                             <xsl:with-param name="timezone" select="$timezone" />
                                             <xsl:with-param name="pattern" select="$pattern" />
                                          </xsl:call-template>
                                       </xsl:when>
                                       <xsl:when test="substring($dt, 11, 1) = 'T' and
                                                       substring($dt, 14, 1) = ':' and
                                                       substring($dt, 17, 1) = ':'">
                                          <xsl:variable name="hour" select="substring($dt, 12, 2)" />
                                          <xsl:variable name="min" select="substring($dt, 15, 2)" />
                                          <xsl:variable name="sec" select="substring($dt, 18)" />
                                          <xsl:if test="$hour &lt;= 23 and
                                                        $min &lt;= 59 and
                                                        $sec &lt;= 60">
                                             <xsl:call-template name="date:_format-date">
                                                <xsl:with-param name="year" select="$year" />
                                                <xsl:with-param name="month" select="$month" />
                                                <xsl:with-param name="day" select="$day" />
                                                <xsl:with-param name="hour" select="$hour" />
                                                <xsl:with-param name="minute" select="$min" />
                                                <xsl:with-param name="second" select="$sec" />
                                                <xsl:with-param name="timezone" select="$timezone" />
                                                <xsl:with-param name="pattern" select="$pattern" />
                                             </xsl:call-template>
                                          </xsl:if>
                                       </xsl:when>
                                    </xsl:choose>
                                 </xsl:if>
                              </xsl:when>
                           </xsl:choose>
                        </xsl:when>
                     </xsl:choose>
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:if>
         </xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:value-of select="$formatted" />   
</xsl:template>

<xsl:template name="date:_format-date">
   <xsl:param name="year" />
   <xsl:param name="month" select="1" />
   <xsl:param name="day" select="1" />
   <xsl:param name="hour" select="0" />
   <xsl:param name="minute" select="0" />
   <xsl:param name="second" select="0" />
   <xsl:param name="timezone" select="'Z'" />
   <xsl:param name="pattern" select="''" />
   <xsl:variable name="char" select="substring($pattern, 1, 1)" />
   <xsl:choose>
      <xsl:when test="not($pattern)" />
      <xsl:when test='$char = "&apos;"'>
         <xsl:choose>
            <xsl:when test='substring($pattern, 2, 1) = "&apos;"'>
               <xsl:text>&apos;</xsl:text>
               <xsl:call-template name="date:_format-date">
                  <xsl:with-param name="year" select="$year" />
                  <xsl:with-param name="month" select="$month" />
                  <xsl:with-param name="day" select="$day" />
                  <xsl:with-param name="hour" select="$hour" />
                  <xsl:with-param name="minute" select="$minute" />
                  <xsl:with-param name="second" select="$second" />
                  <xsl:with-param name="timezone" select="$timezone" />
                  <xsl:with-param name="pattern" select="substring($pattern, 3)" />
               </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
               <xsl:variable name="literal-value" select='substring-before(substring($pattern, 2), "&apos;")' />
               <xsl:value-of select="$literal-value" />
               <xsl:call-template name="date:_format-date">
                  <xsl:with-param name="year" select="$year" />
                  <xsl:with-param name="month" select="$month" />
                  <xsl:with-param name="day" select="$day" />
                  <xsl:with-param name="hour" select="$hour" />
                  <xsl:with-param name="minute" select="$minute" />
                  <xsl:with-param name="second" select="$second" />
                  <xsl:with-param name="timezone" select="$timezone" />
                  <xsl:with-param name="pattern" select="substring($pattern, string-length($literal-value) + 2)" />
               </xsl:call-template>
            </xsl:otherwise>
         </xsl:choose>
      </xsl:when>
      <xsl:when test="not(contains('abcdefghjiklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $char))">
         <xsl:value-of select="$char" />
         <xsl:call-template name="date:_format-date">
            <xsl:with-param name="year" select="$year" />
            <xsl:with-param name="month" select="$month" />
            <xsl:with-param name="day" select="$day" />
            <xsl:with-param name="hour" select="$hour" />
            <xsl:with-param name="minute" select="$minute" />
            <xsl:with-param name="second" select="$second" />
            <xsl:with-param name="timezone" select="$timezone" />
            <xsl:with-param name="pattern" select="substring($pattern, 2)" />
         </xsl:call-template>
      </xsl:when>
      <xsl:when test="not(contains('GyMdhHmsSEDFwWakKz', $char))">
         <xsl:message>
            Invalid token in format string: <xsl:value-of select="$char" />
         </xsl:message>
         <xsl:call-template name="date:_format-date">
            <xsl:with-param name="year" select="$year" />
            <xsl:with-param name="month" select="$month" />
            <xsl:with-param name="day" select="$day" />
            <xsl:with-param name="hour" select="$hour" />
            <xsl:with-param name="minute" select="$minute" />
            <xsl:with-param name="second" select="$second" />
            <xsl:with-param name="timezone" select="$timezone" />
            <xsl:with-param name="pattern" select="substring($pattern, 2)" />
         </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
         <xsl:variable name="next-different-char" select="substring(translate($pattern, $char, ''), 1, 1)" />
         <xsl:variable name="pattern-length">
            <xsl:choose>
               <xsl:when test="$next-different-char">
                  <xsl:value-of select="string-length(substring-before($pattern, $next-different-char))" />
               </xsl:when>
               <xsl:otherwise>
                  <xsl:value-of select="string-length($pattern)" />
               </xsl:otherwise>
            </xsl:choose>
         </xsl:variable>
         <xsl:choose>
            <xsl:when test="$char = 'G'">
               <xsl:choose>
                  <xsl:when test="string($year) = 'NaN'" />
                  <xsl:when test="$year > 0">AD</xsl:when>
                  <xsl:otherwise>BC</xsl:otherwise>
               </xsl:choose>
            </xsl:when>
            <xsl:when test="$char = 'M'">
               <xsl:choose>
                  <xsl:when test="string($month) = 'NaN'" />
                  <xsl:when test="$pattern-length >= 3">
                     <xsl:variable name="month-node" select="document('')/*/date:months/date:month[number($month)]" />
                     <xsl:choose>
                        <xsl:when test="$pattern-length >= 4">
                           <xsl:value-of select="$month-node" />
                        </xsl:when>
                        <xsl:otherwise>
                           <xsl:value-of select="$month-node/@abbr" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$pattern-length = 2">
                     <xsl:value-of select="format-number($month, '00')" />
                  </xsl:when>
                  <xsl:otherwise>
                     <xsl:value-of select="$month" />
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:when>
            <xsl:when test="$char = 'E'">
               <xsl:choose>
                  <xsl:when test="string($year) = 'NaN' or string($month) = 'NaN' or string($day) = 'NaN'" />
                  <xsl:otherwise>
                     <xsl:variable name="month-days" select="sum(document('')/*/date:months/date:month[position() &lt; $month]/@length)" />
                     <xsl:variable name="days" select="$month-days + $day + boolean(((not(boolean($year mod 4)) and $year mod 100) or not(boolean($year mod 400))) and $month > 2)" />
                     <xsl:variable name="y-1" select="$year - 1" />
                     <xsl:variable name="dow"
                                   select="(($y-1 + floor($y-1 div 4) -
                                             floor($y-1 div 100) + floor($y-1 div 400) +
                                             $days) 
                                            mod 7) + 1" />
                     <xsl:variable name="day-node" select="document('')/*/date:days/date:day[number($dow)]" />
                     <xsl:choose>
                        <xsl:when test="$pattern-length >= 4">
                           <xsl:value-of select="$day-node" />
                        </xsl:when>
                        <xsl:otherwise>
                           <xsl:value-of select="$day-node/@abbr" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:when>
            <xsl:when test="$char = 'a'">
               <xsl:choose>
                  <xsl:when test="string($hour) = 'NaN'" />
                  <xsl:when test="$hour >= 12">PM</xsl:when>
                  <xsl:otherwise>AM</xsl:otherwise>
               </xsl:choose>
            </xsl:when>
            <xsl:when test="$char = 'z'">
               <xsl:choose>
                  <xsl:when test="$timezone = 'Z'">UTC</xsl:when>
                  <xsl:otherwise>UTC<xsl:value-of select="$timezone" /></xsl:otherwise>
               </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
               <xsl:variable name="padding">
                  <xsl:choose>
                     <xsl:when test="$pattern-length > 10">
                        <xsl:call-template name="str:padding">
                           <xsl:with-param name="length" select="$pattern-length" />
                           <xsl:with-param name="chars" select="'0'" />
                        </xsl:call-template>
                     </xsl:when>
                     <xsl:otherwise>
                        <xsl:value-of select="substring('0000000000', 1, $pattern-length)" />
                     </xsl:otherwise>
                  </xsl:choose>
               </xsl:variable>
               <xsl:choose>
                  <xsl:when test="$char = 'y'">
                     <xsl:choose>
                        <xsl:when test="string($year) = 'NaN'" />
                        <xsl:when test="$pattern-length > 2"><xsl:value-of select="format-number($year, $padding)" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="format-number(substring($year, string-length($year) - 1), $padding)" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'd'">
                     <xsl:choose>
                        <xsl:when test="string($day) = 'NaN'" />
                        <xsl:otherwise><xsl:value-of select="format-number($day, $padding)" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'h'">
                     <xsl:variable name="h" select="$hour mod 12" />
                     <xsl:choose>
                        <xsl:when test="string($hour) = 'NaN'"></xsl:when>
                        <xsl:when test="$h"><xsl:value-of select="format-number($h, $padding)" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="format-number(12, $padding)" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'H'">
                     <xsl:choose>
                        <xsl:when test="string($hour) = 'NaN'" />
                        <xsl:otherwise>
                           <xsl:value-of select="format-number($hour, $padding)" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'k'">
                     <xsl:choose>
                        <xsl:when test="string($hour) = 'NaN'" />
                        <xsl:when test="$hour"><xsl:value-of select="format-number($hour, $padding)" /></xsl:when>
                        <xsl:otherwise><xsl:value-of select="format-number(24, $padding)" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'K'">
                     <xsl:choose>
                        <xsl:when test="string($hour) = 'NaN'" />
                        <xsl:otherwise><xsl:value-of select="format-number($hour mod 12, $padding)" /></xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'm'">
                     <xsl:choose>
                        <xsl:when test="string($minute) = 'NaN'" />
                        <xsl:otherwise>
                           <xsl:value-of select="format-number($minute, $padding)" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 's'">
                     <xsl:choose>
                        <xsl:when test="string($second) = 'NaN'" />
                        <xsl:otherwise>
                           <xsl:value-of select="format-number($second, $padding)" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'S'">
                     <xsl:choose>
                        <xsl:when test="string($second) = 'NaN'" />
                        <xsl:otherwise>
                           <xsl:value-of select="format-number(substring-after($second, '.'), $padding)" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="$char = 'F'">
                     <xsl:choose>
                        <xsl:when test="string($day) = 'NaN'" />
                        <xsl:otherwise>
                           <xsl:value-of select="floor($day div 7) + 1" />
                        </xsl:otherwise>
                     </xsl:choose>
                  </xsl:when>
                  <xsl:when test="string($year) = 'NaN' or string($month) = 'NaN' or string($day) = 'NaN'" />
                  <xsl:otherwise>
                     <xsl:variable name="month-days" select="sum(document('')/*/date:months/date:month[position() &lt; $month]/@length)" />
                     <xsl:variable name="days" select="$month-days + $day + boolean(((not($year mod 4) and $year mod 100) or not($year mod 400)) and $month > 2)" />
                     <xsl:choose>
                        <xsl:when test="$char = 'D'">
                           <xsl:value-of select="format-number($days, $padding)" />
                        </xsl:when>
                        <xsl:when test="$char = 'w'">
                           <xsl:call-template name="date:_week-in-year">
                              <xsl:with-param name="days" select="$days" />
                              <xsl:with-param name="year" select="$year" />
                           </xsl:call-template>
                        </xsl:when>
                        <xsl:when test="$char = 'W'">
                           <xsl:variable name="y-1" select="$year - 1" />
                           <xsl:variable name="day-of-week" 
                                         select="(($y-1 + floor($y-1 div 4) -
                                                  floor($y-1 div 100) + floor($y-1 div 400) +
                                                  $days) 
                                                  mod 7) + 1" />
                           <xsl:choose>
                              <xsl:when test="($day - $day-of-week) mod 7">
                                 <xsl:value-of select="floor(($day - $day-of-week) div 7) + 2" />
                              </xsl:when>
                              <xsl:otherwise>
                                 <xsl:value-of select="floor(($day - $day-of-week) div 7) + 1" />
                              </xsl:otherwise>
                           </xsl:choose>
                        </xsl:when>
                     </xsl:choose>
                  </xsl:otherwise>
               </xsl:choose>
            </xsl:otherwise>
         </xsl:choose>
         <xsl:call-template name="date:_format-date">
            <xsl:with-param name="year" select="$year" />
            <xsl:with-param name="month" select="$month" />
            <xsl:with-param name="day" select="$day" />
            <xsl:with-param name="hour" select="$hour" />
            <xsl:with-param name="minute" select="$minute" />
            <xsl:with-param name="second" select="$second" />
            <xsl:with-param name="timezone" select="$timezone" />
            <xsl:with-param name="pattern" select="substring($pattern, $pattern-length + 1)" />
         </xsl:call-template>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

<xsl:template name="date:_week-in-year">
   <xsl:param name="days" />
   <xsl:param name="year" />
   <xsl:variable name="y-1" select="$year - 1" />
   <!-- this gives the day of the week, counting from Sunday = 0 -->
   <xsl:variable name="day-of-week" 
                 select="($y-1 + floor($y-1 div 4) -
                          floor($y-1 div 100) + floor($y-1 div 400) +
                          $days) 
                         mod 7" />
   <!-- this gives the day of the week, counting from Monday = 1 -->
   <xsl:variable name="dow">
      <xsl:choose>
         <xsl:when test="$day-of-week"><xsl:value-of select="$day-of-week" /></xsl:when>
         <xsl:otherwise>7</xsl:otherwise>
      </xsl:choose>
   </xsl:variable>
   <xsl:variable name="start-day" select="($days - $dow + 7) mod 7" />
   <xsl:variable name="week-number" select="floor(($days - $dow + 7) div 7)" />
   <xsl:choose>
      <xsl:when test="$start-day >= 4">
         <xsl:value-of select="$week-number + 1" />
      </xsl:when>
      <xsl:otherwise>
         <xsl:choose>
            <xsl:when test="not($week-number)">
               <xsl:call-template name="date:_week-in-year">
                  <xsl:with-param name="days" select="365 + ((not($y-1 mod 4) and $y-1 mod 100) or not($y-1 mod 400))" />
                  <xsl:with-param name="year" select="$y-1" />
               </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
               <xsl:value-of select="$week-number" />
            </xsl:otherwise>
         </xsl:choose>
      </xsl:otherwise>
   </xsl:choose>
</xsl:template>

</xsl:stylesheet>
