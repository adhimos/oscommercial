<?php
/**
 * Created by PhpStorm.
 * User: Diluk
 * Date: 2/13/14
 * Time: 3:34 PM
 */
require('includes/application_top.php');
$start_date =  mktime(0,0,0,1,1,2012);
$end_date =  mktime(0,0,0,1,1,2015);


$fn = $_GET["fn"];


switch($fn)
{
    case "dailyvisits":
        echo get_daily_visit_stats();
        break;
    case "dailyviews":
        echo get_daily_views_stats();
        break;
    case "weeklyvisits":
        echo get_weekly_visit_stats();
        break;
    case "monthlyvisits":
        echo get_monthly_visit_stats();
        break;
    case "monthlyviews":
        echo get_monthly_views_stats();
        break;
    case "allvisits":
        echo get_allvisits();
        break;
    case "allviews":
        echo get_allviews();
        break;
    case "todayvisits":
        echo get_todayvisits();
        break;
    case "todayviews":
        echo get_todayviews();
        break;
    case "maxvisits":
        echo get_maxvistis();
        break;
    case "maxviews":
        echo get_maxviews();
        break;
    case "topcountries":
        echo get_top_countries();
        break;
    case "topproducts":
        echo get_top_products();
        break;
    default:
        echo get_daily_visit_stats();
        break;

}

function get_daily_visit_stats()
{

    $sql_data = tep_db_query("SELECT session_month_string, session_day, COUNT(*) as total FROM ".TABLE_WA_SESSIONS." GROUP BY session_month_string,session_day ORDER BY session_starttime Desc LIMIT 30");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('x' => $row['session_month_string'].' - '.$row['session_day'], 'count' => $row['total']);
    }

    return json_encode(array_reverse($row_array));

}

function get_weekly_visit_stats()
{
    $sql_data = tep_db_query("SELECT session_year, session_month_string, session_week, COUNT(*) as total FROM ".TABLE_WA_SESSIONS." GROUP BY session_week, session_month_string,session_year ORDER BY session_starttime DESC LIMIT 30");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('x' => $row['session_year'].' - Week #'.$row['session_week'], 'count' => $row['total']);
    }

    return json_encode(array_reverse($row_array));

}

function get_monthly_visit_stats()
{
    $sql_data = tep_db_query("SELECT session_year, session_month_string, COUNT(*) as total FROM ".TABLE_WA_SESSIONS." GROUP BY session_month_string,session_year ORDER BY session_starttime DESC LIMIT 30");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('x' => $row['session_month_string'].' - '.$row['session_year'], 'count' => $row['total']);
    }
    return json_encode(array_reverse($row_array));

}

function get_allvisits()
{
    $sql_data = tep_db_query("select count(*) as total_visits FROM ".TABLE_WA_SESSIONS);
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_allviews()
{
    $sql_data = tep_db_query("select count(*) as total FROM ".TABLE_WA_VIEWS);
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_todayvisits()
{
    $sql_data = tep_db_query("select count(*) as total_visits FROM ".TABLE_WA_SESSIONS." WHERE DATE(session_starttime) = DATE(now())");
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_todayviews()
{
    $sql_data = tep_db_query("select count(*) as total FROM ".TABLE_WA_VIEWS." WHERE DATE(view_datetime) = DATE(now())");
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_maxviews()
{
    $sql_data = tep_db_query("select max(total_views) as max_views_day FROM (select count(*) as total_views FROM ".TABLE_WA_VIEWS." group by DATE(view_datetime)) as a;
");
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_maxvistis()
{
    $sql_data = tep_db_query("select max(total_visits) as max_visit_day FROM (select count(*) as total_visits FROM ".TABLE_WA_SESSIONS." group by session_year, session_month, session_day) as a;
");
    return json_encode(tep_db_fetch_array($sql_data));
}

function get_top_countries() {
    $sql_data = tep_db_query("SELECT countries_name as Country, count(*) as Total FROM ".TABLE_WA_SESSIONS. " INNER JOIN ".TABLE_COUNTRIES." on ".TABLE_WA_SESSIONS.".country = ".TABLE_COUNTRIES.".countries_iso_code_2 group by country ORDER by Total desc limit 10");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('Country' => $row['Country'], 'Count' => $row['Total']);
    }

    return json_encode($row_array);

}

function get_top_products() {
    $sql_data = tep_db_query("SELECT products_name as Title, count(*) as Total  FROM ".TABLE_WA_VIEWS." INNER JOIN ".TABLE_PRODUCTS_DESCRIPTION." on ".TABLE_WA_VIEWS.".product_id = ".TABLE_PRODUCTS_DESCRIPTION.".products_id group by ".TABLE_WA_VIEWS.".product_id ORDER by total desc limit 10");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('Title' => $row['Title'], 'Count' => $row['Total']);
    }

    return json_encode($row_array);

}

function get_daily_views_stats()
{
    $sql_data = tep_db_query("SELECT   upper(substring(monthname(view_datetime),1,3)) as M, DAY(view_datetime) as D, COUNT(*) as total FROM ".TABLE_WA_VIEWS." GROUP BY DAY(view_datetime) ORDER BY view_datetime  LIMIT 30");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('x' => $row['M'].' - '.$row['D'], 'count' => $row['total']);
    }
    return json_encode($row_array);

}

function get_monthly_views_stats()
{
    $sql_data = tep_db_query("SELECT  upper(substring(monthname(view_datetime),1,3)) as M, Year(view_datetime) as Y,  COUNT(*) as total FROM ".TABLE_WA_VIEWS." GROUP BY MONTH(view_datetime) ORDER BY view_datetime  LIMIT 30");
    $row_array = null;
    while ($row = tep_db_fetch_array($sql_data)) {
        $row_array[] = array('x' => $row['Y'].' - '.$row['M'], 'count' => $row['total']);
    }
    return json_encode($row_array);

}

