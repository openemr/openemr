/* openemr/library/js/canvas.js
 * Custom file (DRH) for graphic sketchpad functionality
 */

function Sketch(canvasID, linesize=3, linecolor="#FF0000") {

    var canvas
      , ctx
      , lastX=-1
      , lastY=-1
      , mouseX
      , mouseY
      , touchX
      , touchY
      , canvas_left
      , canvas_top
      , drawing = false
      , path = []
      , commands = []
      , MOVE_TO = 0
      , LINE_TO = 1;

    function clearScreen() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }

    function clear() {
        clearScreen();
        document.getElementById('output').value = ''; // DRH addition - removes array from hidden "output" box as well as clearing screen
        path = [];
        commands = [];
    }

    function startLine(x, y) {
        drawing = true;
        ctx.beginPath();
        path.push(x, y);
        commands.push(MOVE_TO);
        ctx.moveTo(x, y);
    }

    function drawPath() {
        var point_x, point_y, command;

        if (path.length === 0) {
            return;
        }

        ctx.beginPath();

        for (var p = 0; p < path.length; p += 2) {

            point_x = path[p];
            point_y = path[p + 1];
            command = commands[Math.floor(p / 2)];
            nextcommand = commands[Math.floor((p / 2) + 1)];

            if (command === MOVE_TO) {
                ctx.moveTo(point_x, point_y);
                // look at next command to determine whether to print a dot or start a line
                if (nextcommand != 1) {
                    drawDot(point_x, point_y);
                }
            } else if (command === LINE_TO) {
                ctx.lineTo(point_x, point_y);
            } else {
                console.log("unknown command");
            }
            ctx.stroke();
        }
    }

    function drawDot(x,y) { // this function probably not necessary - can utilize drawTouchDot instead
		ctx.arc(x, y, Math.floor(linesize / 2), 0, 2 * Math.PI);
		ctx.stroke();
	    ctx.beginPath();
    }

    function drawLine(x, y) {
        path.push(x, y);
        commands.push(LINE_TO);
        clearScreen();
        drawPath();
    }

    function endLine(x, y) {
        drawLine(x, y);
        drawing = false;
    }

    function onMouseDown(e) {
        startLine(Math.floor(e.pageX - canvas_left), Math.floor(e.pageY - canvas_top));
    }

    function onMouseUp(e) {
    	var output; // DRH addition
        endLine(Math.floor(e.pageX - canvas_left), Math.floor(e.pageY - canvas_top));
        output =  getJSON(); // DRH addition
        document.getElementById('output').value = output; // DRH addition - updates output box every time pen is lifted
    }

    function onMouseMove(e) {
        if (drawing) {
            drawLine(Math.floor(e.pageX - canvas_left), Math.floor(e.pageY - canvas_top));
        }
    }

	  function drawTouchLine(ctx,x,y) { // x == mouseX, y == mouseY
	      path.push(x, y);

	      if (lastX == -1) {
	    	    commands.push(MOVE_TO);
	      } else {
	    	    commands.push(LINE_TO);
	      }

	      // If lastX is not set, set lastX and lastY to the current position
	      if (lastX==-1) {
	          lastX=x;
			      lastY=y;
	      }

		    // set line parameters
		    ctx.strokeStyle = linecolor;
	        ctx.lineCap = "round";
		    ctx.lineWidth = linesize;

	      ctx.beginPath();
		    ctx.moveTo(lastX,lastY);
		    ctx.lineTo(x,y);
	      ctx.stroke();

		    // Update the last position to reference the current position
	      lastX=x;
		    lastY=y;
	  }

	  function drawTouchPath() {
		    var point_x, point_y, command;

    		if (path.length === 0) {
		      	return;
		    }

		    ctx.strokeStyle = linecolor;
		    ctx.lineCap = "round";
		    ctx.lineWidth = linesize;

		    ctx.beginPath();

		    for (var p = 0; p < path.length; p += 2) {
			      point_x = path[p];
			      point_y = path[p + 1];
			      command = commands[Math.floor(p / 2)];
			      nextcommand = commands[Math.floor((p / 2) + 1)];

			      if (command === MOVE_TO) {
				        ctx.moveTo(point_x, point_y);
				        if (nextcommand != 1) {
    				        drawTouchDot(point_x, point_y);
				        }
			      } else if (command === LINE_TO) {
				        ctx.lineTo(point_x, point_y);
			      } else {
				        console.log("unknown command");
			      }

	          ctx.stroke();
		    }
	  }

	  function drawTouchDot(x,y) { // primarily for periods when used as a notepad
		    ctx.arc(x, y, Math.floor(linesize / 2), 0, 2 * Math.PI);
		    ctx.stroke();
		    ctx.beginPath();
	  }


    function onTouchStart() {
        // Update the touch coordinates
        getTouchPos();
        drawTouchLine(ctx,touchX,touchY);
        // Prevents an additional mousedown event being triggered
        event.preventDefault();
    }

    function onTouchEnd() {
        // Reset lastX and lastY to -1 to indicate that they are now invalid, since we have difted the "pen"
        lastX=-1;
        lastY=-1;

        // DRH *
        output = getJSON();
        document.getElementById('output').value = output;

    }

    function onTouchMove(e) {
        // Update the touch coordinates
        getTouchPos(e);

        // During a touchmove event, unlike a mousemove event, we don't need to check if the touch is engaged, since there will always be contact with the screen by definition.
        drawTouchLine(ctx,touchX,touchY);

        // Prevent a scrolling action as a result of this touchmove triggering.
        event.preventDefault();
    }

    function getTouchPos(e) {
        if (!e)
            var e = event;

        if (e.touches) {
            if (e.touches.length == 1) { // Only deal with one finger
                var touch = e.touches[0]; // Get the information for finger #1
                touchX=Math.floor(touch.pageX-touch.target.offsetLeft);
                touchY=Math.floor(touch.pageY-touch.target.offsetTop);
            }
        }
    }

    /**
     * Return array of points normalized to be between 0 & 1
     * @return {Array}
     */
    function normalizePoints(points) {
        var point, normalArray = [];
        for (var p = 0; p < points.length; p += 2) {
            point = points[p];
            normalArray.push(points[p] / canvas.width, points[p + 1] / canvas.height);
        }
        return normalArray;
    }

    function deNormalizePoints(points) {
        var point, deNormalArray = [];
        for (var p = 0; p < points.length; p += 2) {
            point = points[p];
            deNormalArray.push(points[p] * canvas.width, points[p + 1] * canvas.height);
        }
        return deNormalArray;
    }

    function getData(normalize) {
        //return as array of point objects
        if (normalize) {
            return normalizePoints(path);
        } else {
            return path;
        }
    }

    function getJSON(normalize) {

        var point, points;
        if (normalize) {
            points = normalizePoints(path);
        } else {
            points = path;
        }

        return '[[' + points + '], [' + commands + ']]';
    }

    function setData(pathData, normalized) {
        clear();
        if (normalized) {
            path = deNormalizePoints(pathData[0]);
        } else {
            path = pathData[0];
        }
        commands = pathData[1];
        //drawPath();
        drawTouchPath();
    }

    function loadJSON(json, normalized) {
        var pathData;
        if (json) {
            clear();
            try {
                pathData = eval(json);
            } catch (e) {
                alert("error parsing json -- please check with jslint");
            }
            setData(pathData, normalized);
        }
    }

    function init(canvasID) {

        if (typeof canvasID !== "string") {
            alert("Drawing requires id of canvas element as parameter");
            return;
        }

        canvas = document.getElementById(canvasID);

        if (!canvas) {
            alert("Drawing was not able to find a canvas element with ID == ".canvasID);
        }

        try {
            ctx = canvas.getContext("2d");
        } catch (e) {
            alert("Drawing was unable to initialize. Most likely you browser does not support Canvas");
            return;
        }

        canvas_left = $(canvas).position().left;
        canvas_top = $(canvas).position().top;
        ctx.strokeStyle = linecolor;
        ctx.lineWidth = linesize;

        canvas.addEventListener('mousedown', onMouseDown, false);
        canvas.addEventListener('mouseup', onMouseUp, false);
        canvas.addEventListener('mousemove', onMouseMove, false);

        canvas.addEventListener('touchstart', onTouchStart, false);
        canvas.addEventListener('touchend', onTouchEnd, false);
        canvas.addEventListener('touchmove', onTouchMove, false);
    }

    init(canvasID);

    this.clear = clear;
    this.getData = getData;
    this.setData = setData;
    this.getJSON = getJSON;
    this.loadJSON = loadJSON;

}
