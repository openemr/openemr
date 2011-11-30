    function updateApptTime(marker,index,y,date,provider)
    {
        row=$("#times table tr").eq(index);
        timeSlot=row.find("td a");
        params=timeSlot.attr("href").split("(")[1].split(",");
        newEvtParam=params[0]+","+params[1]+","+params[2]+","+date+","+provider+","+"0";
        onClk="javascript:newEvt("+newEvtParam+")";
        marker.html(timeSlot.html());
        marker.attr("href",onClk);
    }
    function displayApptTime(evt)
    {
        
        marker=$(this).find("a.apptMarker");
        if(marker.length==0)
        {
            style="style=\'height:"+tsHeight+";\'"
            $(this).find("div.calendar_day").append("<a class=\'apptMarker event event_appointment\'"+style+">");
            marker=$(this).find("a.apptMarker");
            marker.css("z-index",1);
        }
        y=evt.pageY-$(this).offset().top;
        rem=y % tsHeightNum;
        y=y-rem;
        ph=$(this).find("div.providerheader");
        index=y/tsHeightNum;
        if(ph.length==1)
            {
                y=y-ph.height();
                if(index==0)
                    {
                        marker.hide();
                    }
            }
        marker.css("top",y);
        date=$(this).attr("date");
        updateApptTime(marker,index,y,date,$(this).attr("provider"));
        marker.show();
    }
    function hideApptTime(evt)
    {
        marker=$(this).find("a.apptMarker");
        marker.hide();
    }
    function setupDirectTime()
    {
        $("td.schedule").mousemove(displayApptTime);
        $("td.schedule").mouseout(hideApptTime);
    }