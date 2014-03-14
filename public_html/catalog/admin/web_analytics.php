<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/13/14
 * Time: 2:27 PM
 */
require('includes/application_top.php');
require(DIR_WS_INCLUDES . 'template_top.php');

?>

<script type="text/javascript">


    function changeTab(event, isVisit) {
        if(isVisit){changeTab
            var chartInterval = $('#visitTabs').jqxTabs('getTitleAt', event.args.item);
            load_chart(chartInterval, true);
        }
        else{
            var chartInterval = $('#viewTabs').jqxTabs('getTitleAt', event.args.item);
            load_chart(chartInterval, false);
        }
    }


    function load_chart(chartInterval, isVisit) {
        var chartType = "Views";
        if(isVisit)
            chartType = "Visits"
        // prepare chart data
        var source = {
            datatype: "json",
            datafields:
                [
                    { name: 'x', type: 'string'},
                    { name: 'count', type: 'int'}
                ],
            cache: false,
            type: "GET",
            url: 'web_analytics_data.php?fn=' + chartInterval.toLowerCase() + chartType.toLowerCase()
        }
        var dataadapter = new $.jqx.dataAdapter(source);

        var settings = {
            title: chartInterval + " " + chartType,
            description: chartInterval+ " " +chartType+" Count",
            padding: { left: 5, top: 5, right: 5, bottom: 5 },
            titlePadding: { left: 90, top: 0, right: 0, bottom: 10 },
            showLegend: false,
            source: dataadapter,
            categoryAxis:
            {
                dataField: 'x',
                formatFunction: function (value) {
                    return value;

                },
                showGridLines: true
            },
            colorScheme: 'scheme01',
            renderEngine: 'HTML5',
            seriesGroups:
                [
                    {
                        type: 'column',
                        columnsGapPercent: 30,
                        seriesGapPercent: 0,

                        valueAxis:
                        {
                            minValue: 0,

                            description: chartType + ' Count'
                        },
                        series: [
                            { dataField: 'count', displayText: 'Count'}

                        ]
                    }
                ]
        };

        // select the chartContainer DIV element and render the chart.
if(isVisit)
        $('#'+chartInterval.toLowerCase()+'-visits').jqxChart(settings);
        else
    $('#'+chartInterval.toLowerCase()+'-views').jqxChart(settings);
    }

    function load_top_products() {
        console.log('loading top products');
        // prepare chart data
        var source = {
            datatype: "json",
            datafields:
                [
                    { name: 'Title', type: 'string'},
                    { name: 'Count', type: 'int'}
                ],
            cache: false,
            type: "GET",
            url: 'web_analytics_data.php?fn=topproducts'
        }
        var dataadapter = new $.jqx.dataAdapter(source);
        console.log('initialize top product grid');
        // initialize grid
        $("#topProductsGrid").jqxGrid(
            {
                width: 400,
                source: dataadapter,
                autoheight: true,
                altrows: true,
                selectionmode:'none',
                columns: [
                    { text: 'Product Title',  datafield: 'Title', width: 300 },
                    { text: 'Total Views', datafield: 'Count', width: 100 }
                  ]
            });
    }

    function load_top_countries() {
        console.log('loading top customers');
        // prepare chart data
        var source = {
            datatype: "json",
            datafields:
                [
                    { name: 'Country', type: 'string'},
                    { name: 'Count', type: 'int'}
                ],
            cache: false,
            type: "GET",
            url: 'web_analytics_data.php?fn=topcountries'
        }
        var dataadapter = new $.jqx.dataAdapter(source);
        console.log('initialize top countries grid');
        // initialize grid
        $("#visitByCountriesGrid").jqxGrid(
            {
                width: 400,
                source: dataadapter,
                autoheight: true,
                altrows: true,
                selectionmode:'none',
                columns: [
                    { text: 'Country',  datafield: 'Country', width: 300 },
                    { text: 'Total Views', datafield: 'Count', width: 100 }
                ]
            });
    }

    jQuery(document).ready(function () {

        $('#visitTabs').on('selected', function (event) {
            changeTab(event, true);
        });
        $('#viewTabs').on('selected', function (event) {
            changeTab(event, false);
        });

        $('#visitTabs').jqxTabs({ height: 400 });
        $('#viewTabs').jqxTabs({ height: 400 });
        load_chart("Daily", true);
        load_chart("Daily", false);
        $.getJSON( "web_analytics_data.php?fn=allvisits", function( json ) {
            $('#all-visits').html(json.total_visits);
        });
        $.getJSON( "web_analytics_data.php?fn=maxvisits", function( json ) {
            $('#max-visits').html(json.max_visit_day);
        });
        $.getJSON( "web_analytics_data.php?fn=todayvisits", function( json ) {
            $('#today-visits').html(json.total_visits);
        });
        $.getJSON( "web_analytics_data.php?fn=allviews", function( json ) {
            $('#all-views').html(json.total);
        });
        $.getJSON("web_analytics_data.php?fn=maxviews", function( json ) {
            $('#max-views').html(json.max_views_day);
        });
        $.getJSON( "web_analytics_data.php?fn=todayviews", function( json ) {
            $('#today-views').html(json.total);
        });

        load_top_products();
        load_top_countries();
    });
</script>

<h3>Visits Statistics</h3>
<div style="margin: 5px;">
    <div id="visitTabs">
        <ul>
            <li style="margin-left: 20px;">Daily</li>
            <li>Weekly</li>
            <li>Monthly</li>
        </ul>
        <div id="daily-visits"></div>
        <div id="weekly-visits"></div>
        <div id="monthly-visits"></div>
    </div>
</div>
<h4>Visit Statistics Summary</h4>
<ul>
    <li>Today Visits <span id="today-visits"</span></li>
    <li>Max Visits <span id="max-visits"</span></li>
    <li>All Visits <span id="all-visits"</span></li>
</ul>
<h3>View Statistics</h3>
<div style="margin: 5px;">
    <div id="viewTabs">
        <ul>
            <li style="margin-left: 20px;">Daily</li>
            <li>Monthly</li>
        </ul>
        <div id="daily-views"></div>
        <div id="monthly-views"></div>
    </div>
</div>
<h4>View Statistics Summary</h4>
<ul>
    <li>Today Views <span id="today-views"</span></li>
    <li>Max Views <span id="max-views"</span></li>
    <li>All Views <span id="all-views"</span></li>
</ul>
<!--<div id="stats-nuggets">
    <ul>
        <li><h3>Today</h3>
        <span>0</span> views
        </li>
        <li id="bestever">
            <h3>Best ever</h3>
            <span>15</span>
            <em>views</em>
        </li><li>
       <h3>All time</h3><strong>
                <span>2,055</span> <em>views</em></strong></li>		</ul>
</div>-->
<H3>Top Viewed Products</H3>
<div id="topProductsGrid" ></div>

<H3>Visits by Countries</H3>
<div id="visitByCountriesGrid" ></div>







