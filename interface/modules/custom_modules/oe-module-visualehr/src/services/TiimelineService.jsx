import { format } from "date-fns";
import { UtilService } from "./UtilService";

export const TimelineService = {
    validator: (data) => {
        var encouter = validateEncounter(data.encounters)
        var timeline = data.issues
        var calendar = validateCalendar(data.groupDate)
        return { encouter: encouter, timelines: timeline,calendars: calendar}
    },
};
const validateEncounter=(data)=>{
    var encounterlist = {};
    var encounterMonthlist = {};
    var encounterDaylist = {};
    
    data.forEach(function (val) {
         var date = format(new Date(UtilService.serializeDate(val.date)), "yyyy");
         var monthdate = format(new Date(UtilService.serializeDate(val.date)), "yyyy-MM");
         var datedate = format(new Date(UtilService.serializeDate(val.date)), "yyyy-MM-dd");

        /**
         *  years
         */
        if (!encounterlist[date]) {
            encounterlist[date] = [];
          }
          var datafound = encounterlist[date].find((obj) => {
            return obj.id === val.id;
          });
  
          if (!datafound) {
            encounterlist[date].push(val);
          }
  
          /**
           *  months
           */
          if (!encounterMonthlist[monthdate]) {
            encounterMonthlist[monthdate] = [];
          }
          var encounterfound = encounterMonthlist[monthdate].find((obj) => {
            return obj.id === val.id;
          });
  
          if (!encounterfound) {
            encounterMonthlist[monthdate].push(val);
          }
          /**
           * days
           */
          if (!encounterDaylist[datedate]) {
            encounterDaylist[datedate] = [];
          }
          var encounterdayfound = encounterDaylist[datedate].find((obj) => {
            return obj.id === val.id;
          });
  
          if (!encounterdayfound) {
            encounterDaylist[datedate].push(val);
          }
    })
    return { years: encounterlist, months: encounterMonthlist,days: encounterDaylist}
}

const validateCalendar=(data)=>{
    var groupMonths = {};
    var groupDays = {};
        var datelist = [...data];
        datelist.forEach(function (val) {
          var date = UtilService.serializeDate(val)
          if(!Number.isNaN(new Date(date))){
            var date = format(new Date(date), "yyyy-MM-dd")
            var data = date.split("-");
            var year = data[0];
            var month = year + "-" + data[1];
      
            if (!groupMonths[year]) {
              groupMonths[year] = [];
            }
            if(!groupMonths[year].some(item => month === item))
              groupMonths[year].push(month);
            
            if (!groupDays[year]) {
              groupDays[year] = [];
            }
            if(!groupDays[year].some(item => val === item))
              groupDays[year].push(date);
          }
          
        });
        return Object.keys(groupMonths).map((year) => {
          return {
            year,
            months: groupMonths[year],
            days:groupDays[year]
          };
    });
}
