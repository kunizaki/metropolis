/**
 * @package mod_spproperty_emi_calculator
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

"use strict";
(function ($, window, document, undefined) {
    var name = 'spemicalc';

    function Plugin(element, options)
    {
        this.element = element;
        this.name = name;
        this._defaults = $.fn.spemicalc.defaults;
        this.options = $.extend({}, this._defaults, options);
        this.grid = [];
        this.myChart;
        if (this.options.module_id === null) {
            console.error("You have to provide module id");
            return false;
        }
        this.init();
    }

    $.extend(Plugin.prototype, {
        init: function () {
            this.cacheElement();
            this.calculateEMI();
            if (this.options.autosubmit) {
                $('#mod-sp-property-emi-calculator' + this.options.module_id + ' #spec-form' + this.options.module_id).find('.spec-graph').show();
                $('#mod-sp-property-emi-calculator' + this.options.module_id + ' #spec-form' + this.options.module_id).submit();
            }
        },

        cacheElement: function () {
            this.$element = $(this.element);
        },

        calculateEMI: function () {
            let self = this;
            $('#mod-sp-property-emi-calculator' + this.options.module_id + ' #spec-form' + this.options.module_id)
                .on('submit', function (event) {
                    event.preventDefault();
                    let $this = $(this);
                    let loan_amount = parseFloat($this.find('#spec-load-amount').val());
                    let interest = parseFloat($this.find('#spec-interest').val());
                    let tenure_year = parseInt($this.find('#spec-tenure-period-year').val());
                    let tenure_month = $this.find('#spec-tenure-period-month').val();

                    if (tenure_month == '') {
                        tenure_month = 0;
                    } else {
                        tenure_month = parseInt(tenure_month);
                    }
                    let period = tenure_year * 12 + tenure_month;

                    let r = (interest / 12) / 100;
                    let n = period;
                    let p = parseFloat(self.clone(loan_amount));

                    let a = Math.pow((1 + r), n);

                    let emi;
                    emi = Math.round((p * r * a) / (a - 1));

                    let interest_payable = (emi * period) - loan_amount;

                    let $spec_graph = $this.find('.spec-graph');
                    let $emi_result = $this.find('.spec-display-info .emi-result-value');
                    let $interest_payable = $this.find('.spec-display-info .interest-payable-value');
                    let $principal_and_interest = $this.find('.spec-display-info .principal-and-interest-value');

                    //$emi_result.text(self.options.currency + ' ' + self.formatCurrency(emi));
                    $interest_payable.text(self.options.currency + ' ' + self.formatCurrency(interest_payable));
                    $principal_and_interest.text(self.options.currency + ' ' + self.formatCurrency((emi * period)));
                    $spec_graph.show();
                    let total_interest = (interest_payable * 100) / (loan_amount + interest_payable);
                    let principal_payable = 100 - total_interest;
                    let graphData = [];
                    graphData.push({
                        label: 'Total Interest ',
                        data: total_interest.toFixed(2)
                    });
                    graphData.push({
                        label: 'Principal loan amount ',
                        data: principal_payable.toFixed(2)
                    });
                    self.displayGraph(graphData, self.options.currency + self.formatCurrency(emi));

                });
        },
        clone: function (value) {
            return JSON.parse(JSON.stringify(value));
        },
        formatCurrency: function (money, fixed = 2) {
            return money.toFixed(fixed).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        },
        displayGraph: function (data, emi) {
            if (typeof this.myChart != "undefined") {
                this.myChart.destroy();
            }
            let ctx = document.getElementById("spec-chart" + this.options.module_id).getContext('2d');

            let labels = [];
            let values = [];

            data.forEach(function (value) {
                labels.push(value.label);
                values.push(value.data);
                labels = labels.reverse();
                values = values.reverse();
            });



            // round corners
            Chart.pluginService.register({
                afterUpdate: function (chart) {
                    if (chart.config.options.elements.arc.roundedCornersFor !== undefined) {
                        var arc = chart.getDatasetMeta(0).data[chart.config.options.elements.arc.roundedCornersFor];
                        arc.round = {
                            x: (chart.chartArea.left + chart.chartArea.right) / 2,
                            y: (chart.chartArea.top + chart.chartArea.bottom) / 2,
                            radius: (chart.outerRadius + chart.innerRadius) / 2,
                            thickness: (chart.outerRadius - chart.innerRadius) / 2 - 1,
                            backgroundColor: arc._model.backgroundColor
                        }
                    }
                },

                afterDraw: function (chart) {
                    if (chart.config.options.elements.arc.roundedCornersFor !== undefined) {
                        var ctx = chart.chart.ctx;
                        var arc = chart.getDatasetMeta(0).data[chart.config.options.elements.arc.roundedCornersFor];
                        var startAngle = Math.PI / 2 - arc._view.startAngle;
                        var endAngle = Math.PI / 2 - arc._view.endAngle;

                        ctx.save();
                        ctx.translate(arc.round.x, arc.round.y);
                        ctx.fillStyle = arc.round.backgroundColor;
                        ctx.beginPath();
                        ctx.arc(arc.round.radius * Math.sin(startAngle), arc.round.radius * Math.cos(startAngle), arc.round.thickness, 0, 2 * Math.PI);
                        ctx.arc(arc.round.radius * Math.sin(endAngle), arc.round.radius * Math.cos(endAngle), arc.round.thickness, 0, 2 * Math.PI);
                        ctx.closePath();
                        ctx.fill();
                        ctx.restore();
                    }
                },
            });

            // write text plugin
            Chart.pluginService.register({
                afterUpdate: function (chart) {
                    if (chart.config.options.elements.center) {
                        var helpers = Chart.helpers;
                        var centerConfig = chart.config.options.elements.center;
                        var globalConfig = Chart.defaults.global;
                        var ctx = chart.chart.ctx;

                        var fontStyle = helpers.getValueOrDefault(centerConfig.fontStyle, globalConfig.defaultFontStyle);
                        var fontFamily = helpers.getValueOrDefault(centerConfig.fontFamily, globalConfig.defaultFontFamily);

                        if (centerConfig.fontSize) {
                            var fontSize = centerConfig.fontSize;
                        }
                        // figure out the best font size, if one is not specified
                        else {
                            ctx.save();
                            var fontSize = helpers.getValueOrDefault(centerConfig.minFontSize, 1);
                            var maxFontSize = helpers.getValueOrDefault(centerConfig.maxFontSize, 256);
                            var maxText = helpers.getValueOrDefault(centerConfig.maxText, centerConfig.text);

                            do {
                                ctx.font = helpers.fontString(fontSize, fontStyle, fontFamily);
                                var textWidth = ctx.measureText(maxText).width;

                                // check if it fits, is within configured limits and that we are not simply toggling back and forth
                                if (textWidth < chart.innerRadius * 2 && fontSize < maxFontSize) {
                                    fontSize += 1;
                                } else {
                                    // reverse last step
                                    fontSize -= 1;
                                    break;
                                }
                            } while (true) {
                                ctx.restore();
                            }
                        }

                        // save properties
                        chart.center = {
                            font: helpers.fontString(fontSize, fontStyle, fontFamily),
                            fillStyle: helpers.getValueOrDefault(centerConfig.fontColor, globalConfig.defaultFontColor)
                        };
                    }
                },
                afterDraw: function (chart) {
                    if (chart.center) {
                        var centerConfig = chart.config.options.elements.center;
                        var ctx = chart.chart.ctx;

                        ctx.save();
                        ctx.font = chart.center.font;
                        ctx.fillStyle = chart.center.fillStyle;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                        var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2.2;
                        ctx.fillText(centerConfig.text, centerX, centerY);
                        ctx.restore();
                    }
                },
            });
            //Subtitle
            Chart.pluginService.register({
                afterUpdate: function (chart) {
                    if (chart.config.options.elements.subtitle) {
                        var helpers = Chart.helpers;
                        var subConfig = chart.config.options.elements.subtitle;
                        var globalConfig = Chart.defaults.global;
                        var ctx = chart.chart.ctx;

                        var fontStyle = helpers.getValueOrDefault(subConfig.fontStyle, globalConfig.defaultFontStyle);
                        var fontFamily = helpers.getValueOrDefault(subConfig.fontFamily, globalConfig.defaultFontFamily);

                        if (subConfig.fontSize) {
                            var fontSize = subConfig.fontSize;
                        }
                        // figure out the best font size, if one is not specified
                        else {
                            ctx.save();
                            var fontSize = helpers.getValueOrDefault(subConfig.minFontSize, 1);
                            var maxFontSize = helpers.getValueOrDefault(subConfig.maxFontSize, 256);
                            var maxText = helpers.getValueOrDefault(subConfig.maxText, subConfig.text);

                            do {
                                ctx.font = helpers.fontString(fontSize, fontStyle, fontFamily);
                                var textWidth = ctx.measureText(maxText).width;

                                // check if it fits, is within configured limits and that we are not simply toggling back and forth
                                if (textWidth < chart.innerRadius * 2 && fontSize < maxFontSize) {
                                    fontSize += 1;
                                } else {
                                    // reverse last step
                                    fontSize -= 1;
                                    break;
                                }
                            } while (true) {
                                ctx.restore();
                            }
                        }

                        // save properties
                        chart.center = {
                            font: helpers.fontString(fontSize, fontStyle, fontFamily),
                            fillStyle: helpers.getValueOrDefault(subConfig.fontColor, subConfig.defaultFontColor)
                        };
                    }
                },
                afterDraw: function (chart) {
                    if (chart.center) {
                        var subConfig = chart.config.options.elements.subtitle;
                        var ctx = chart.chart.ctx;

                        ctx.save();
                        ctx.font = chart.center.font;
                        ctx.fillStyle = chart.center.fillStyle;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'baseline';
                        var centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                        var centerY = (chart.chartArea.top + chart.chartArea.bottom) / 1.6;
                        ctx.fillText(subConfig.text, centerX, centerY);
                        ctx.restore();
                    }
                },
            });



            this.myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        label: '',
                        data: values,
                        backgroundColor: [
                            '#20D8D3',
                            '#217874',

                        ],
                        borderColor: [
                            '#20D8D3',
                            '#217874',
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    legend: {
                        display: false
                    },
                    cutoutPercentage: 75,
                    rotate: 0.5 * Math.PI,
                    tooltip: {
                        backgroundColor: 'black'
                    },
                    elements: {
                        arc: {
                            roundedCornersFor: 0
                        },
                        center: {
                            maxText: '100%',
                            text: emi,
                            fontColor: '#1A7874',
                            fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                            fontStyle: 'normal',
                            fontSize: 13,
                            minFontSize: 1,
                            maxFontSize: 256,
                        },
                        subtitle: {
                            maxText: '100%',
                            text: '(per month)',
                            fontColor: '#1A7874',
                            fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                            fontStyle: 'normal',
                            fontSize: 13,
                            minFontSize: 1,
                            maxFontSize: 256,
                        }
                    }
                }
            });
        }
    });

    $.fn.spemicalc = function (options) {
        this.each(function () {
            if (!$.data(this, name)) {
                $.data(this, name, new Plugin(this, options));
            }
        });
        return this;
    }

    $.fn.spemicalc.defaults = {
        module_id: null,
        currency: '$',
        autosubmit: false
    };
})(jQuery, window, document);