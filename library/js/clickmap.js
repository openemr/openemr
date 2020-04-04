var clickmap = function(args) {

	let f = true;
	let counter = 0;

	const fn_buildMarker = function(x, y, pos, annotation) {
        const legendItem = $("<li class='legend-item'><b>" + pos + "</b> " + decodeURIComponent(annotation) + "</li>");
        $(".legend .body ul").append(legendItem);

        const marker = $(".marker-template").clone();
        marker.attr("data-x", x).attr("data-y", y).attr("data-pos", pos).attr("id", new Date().getTime())
            .attr("class", "marker")
            .attr("style", "left:" + x + "px; top:" + y + "px;" )
            .find("span.count").text( pos );

        marker.mouseenter( function() {} )
            .mouseleave( function() { f = false; } )
            .attr("title", annotation ? decodeURIComponent(annotation) : "" )
            .show()
            .click( function() { $(this).remove(); legendItem.remove(); f = false; } );
            return marker;
	};

	const fn_isnumber = function(num) {
        return !isNaN(parseInt(num));
    }

	const fn_clear = function() {
		$(".marker").remove();
		$(".legend-item").remove();
		counter = 0;
	};

	const fn_load = function(container, val) {
		fn_clear();
        if (!val) {
            return;
        }
		const coordinates = val.split("}");
		for (let i = 0; i < coordinates.length; i++) {
			const coordinate = coordinates[i];
			if (coordinate) {
				const info = coordinate.split("^");
                const x = info[0];
                const y = info[1];
                const label = info[2];
                const detail = info[3];
				const marker = fn_buildMarker(x, y, label, detail);
				container.append(marker);
				if (fn_isnumber(label)) {
				    counter = parseInt(label);
				}
			}
		}
	};

	const fn_save = function() {
		let val = "";
		$(".marker").each( function() {
			const marker = $(this);
			val += marker.attr("data-x") + "^" + marker.attr("data-y") + "^" + marker.attr("data-pos") + "^" + encodeURIComponent(marker.attr("title")) + "}";
		});
		$("#data").attr("value", val);
        $("#submitForm").submit();
	};


	//// main
	const dropdownOptions = args.dropdownOptions;
	const options = dropdownOptions.options;
	const optionsLabel = dropdownOptions.label;
	let container = args.container;
    const data = args.data;
    const hideNav = args.hideNav;
    let optionsTitle = '';
    let optionsSelect = '';

	container.mousemove(function() {
        f = true;
    });

    if (!hideNav) {
        container.click ( function(e) {
            if (!f) {
                return;
            }
            const x = e.pageX - this.offsetLeft - 5;
            const y = e.pageY - this.offsetTop - 5;
            const hasOptions = typeof(options) != "undefined";

            if (hasOptions) {
                optionsTitle = typeof(optionsLabel) != "undefined" ? optionsLabel : "Select one";
                for (let attr in options) {
                    if (options.hasOwnProperty(attr)) {
                        optionsSelect+= "<option value='" + attr + "'>" + options[attr] + "</option>";
                    }
                }
            }

            const do_marker = function() {
                const newcounter = $('#counterInput').val();
                const notes = encodeURIComponent($('#detailTextArea').val());
                const selectedOption = encodeURIComponent($('#painScaleSelect').val());

                let combinedNotes = "";
                if (selectedOption) {
                    combinedNotes = options[selectedOption];
                }
                if (selectedOption && notes) {
                    combinedNotes += "%3A%20";
                }
                if (notes) {
                    combinedNotes += notes;
                }

                const marker = fn_buildMarker(x, y, newcounter, combinedNotes);
                container.append(marker);
                if (fn_isnumber(newcounter)) {
                    counter++;
                }
            };

            dlgopen('', '', hasOptions? 345 : 300, 350, false, xl('Information'), {
                buttons: [{
                    text: xl('Save'),
                    close: true,
                    style: 'btn-sm btn-primary',
                    click: do_marker,
                }, {
                    text: xl('Cancel'),
                    close: true,
                    style: 'btn-sm btn-secondary'}],
                type: 'Alert',
                html: `
                    <div class="form-group">
                        <label for="counterInput">${xl('Label')}</label>
                        <input type="text" class="form-control" id="counterInput" value="${counter + 1}">
                    </div>
                    <div class="form-group">
                        <label for="painScaleSelect">${optionsTitle}</label>
                        <select class="form-control" id="painScaleSelect">
                            ${optionsSelect}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="detailTextArea">${xl('Detail')}</label>
                        <textarea class="form-control" id="detailTextArea" rows="3"></textarea>
                    </div>`,
            });
        });
    }

	$("#btn_clear").click(fn_clear);
	$("#btn_save").click(fn_save);

	fn_load(container, data);

    if (hideNav) {
        $(".nav").hide();
    };
};
