/* {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} */
jQuery(document).ready(function ($) {
	if (window.loadInRelationTomeControl == undefined) {
		jQuery.Class("OSSTimeControl_Calendar_Js", {
		}, {
			chart: false,
			loadChart: function () {
				var data = $('.sumaryRelatedTimeControl .widgetData').val();
				if (data == undefined || data == '') {
					return false;
				}
				var jdata = JSON.parse(data);
				console.log(jdata);
				var ctx = document.getElementById("related-summary-chart-canvas").getContext("2d");
				var relativeChart = new Chart(ctx, {
					type: 'bar',
					data: jdata,
					options: {
						zoom: {
							enabled: true,
							mode: 'y',
						},
						legend: {
							display: false,
						},
						maintainAspectRatio: false,
						scales: {
							yAxes: [{
									ticks: {
										beginAtZero: true
									}
								}]
						}
					}
				});
				//$.plot(this.chart, [chartData], options);
			},
			registerSwitch: function () {
				$(".sumaryRelatedTimeControl .switchChartContainer").toggle(function () {
					$(this).find('.glyphicon').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
					$(".chartContainer").hide();
				}, function () {
					$(this).find('.glyphicon').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
					$(".chartContainer").show();
				});
			},
			registerEvents: function () {
				this.chart = $('.sumaryRelatedTimeControl .chartBlock');
				this.loadChart();
				this.registerSwitch();
			}
		});
	}
	var instance = new OSSTimeControl_Calendar_Js();
	instance.registerEvents();
	window.loadInRelationTomeControl = true;
});
