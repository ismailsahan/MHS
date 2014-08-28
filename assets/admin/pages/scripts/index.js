var Index = function () {

	function showChartTooltip(x, y, xValue, yValue) {
		$('<div id="tooltip" class="chart-tooltip">' + yValue + '<\/div>').css({
			position: 'absolute',
			display: 'none',
			top: y - 40,
			left: x - 40,
			border: '0px solid #ccc',
			padding: '2px 6px',
			'background-color': '#fff'
		}).appendTo("body").fadeIn(200);
	}

	function showgraph(mhdata) {
		var previousPoint2 = null;
		$('#manhour_stat_loading').hide();
		$('#manhour_stat_content').show();

		$.plot($("#manhour_stat"),
			[{
				data: mhdata,
				lines: {
					fill: 0.2,
					lineWidth: 0,
				},
				color: ['#BAD9F5']
			}, {
				data: mhdata,
				points: {
					show: true,
					fill: true,
					radius: 4,
					fillColor: "#9ACAE6",
					lineWidth: 2
				},
				color: '#9ACAE6',
				shadowSize: 1
			}, {
				data: mhdata,
				lines: {
					show: true,
					fill: false,
					lineWidth: 3
				},
				color: '#9ACAE6',
				shadowSize: 0
			}], {
				xaxis: {
					tickLength: 0,
					tickDecimals: 0,
					mode: "categories",
					min: 0,
					font: {
						lineHeight: 18,
						style: "normal",
						variant: "small-caps",
						color: "#6F7B8A"
					}
				},
				yaxis: {
					ticks: 5,
					tickDecimals: 0,
					tickColor: "#eee",
					font: {
						lineHeight: 14,
						style: "normal",
						variant: "small-caps",
						color: "#6F7B8A"
					}
				},
				grid: {
					hoverable: true,
					clickable: true,
					tickColor: "#eee",
					borderColor: "#eee",
					borderWidth: 1
				}
			}
		);

		$("#manhour_stat").bind("plothover", function (event, pos, item) {
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));
			if (item) {
				if (previousPoint2 != item.dataIndex) {
					previousPoint2 = item.dataIndex;
					$("#tooltip").remove();
					var x = item.datapoint[0].toFixed(2),
						y = item.datapoint[1].toFixed(2);
					showChartTooltip(item.pageX, item.pageY, item.datapoint[0], item.datapoint[1]);
				}
			}
		});

		$('#manhour_stat').bind("mouseleave", function () {
			$("#tooltip").remove();
		});
	}

	return {

		init: function () {
			$.get("{U api/monthdata}", function(data) {
				showgraph(data);
			});
		}

	};

}();