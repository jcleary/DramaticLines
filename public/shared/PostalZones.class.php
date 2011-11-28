<?php
// **************************************************************************
// PostalZones.class.php
//
// class for sorting postal zones and working out postal charges
// --------------------------------------------------------------------------


class PostalZones {

	// ----------------------------------------------------------------------
	// showCountries
	//
	//
	// ----------------------------------------------------------------------
	function showCountries()
	{
		$xtpl = new XTemplate('show_countries.xtpl', '../xtpl/setup');
				
		$recCount = 0;
		
		switch ($_REQUEST['type']) {
				
			case 'assigned':
				$whereClause = 'where cou_zone is not null';
			break;
		
			case 'unassigned':
				$whereClause = 'where cou_zone is null';
			break;
		
			case 'all':
			default:
				$whereClause = '';
			break;			
		}
		connect_db();
		
		$qry = mysql_query("select count(*) cnt from countries $whereClause");
		$row = mysql_fetch_array($qry);
		$rowCount = $row['cnt'];
		
		$halfWay = $rowCount / 2;
		
		$qry = mysql_query("select * from countries left join postal_zones on countries.cou_zone = postal_zones.zon_id $whereClause order by zon_name, cou_name");
		while ($row = mysql_fetch_array($qry)) {
			$recCount ++;
			$xtpl->assign($row);			
			if ($recCount <= $halfWay) {
				$xtpl->parse('main.country_1');
			} else {
				$xtpl->parse('main.country_2');
			}
		}	
		
		$qry = mysql_query("select * from postal_zones");
		while ($row = mysql_fetch_array($qry)) {
			$xtpl->assign($row);
			$xtpl->parse('main.zone');
		}	
		
		$xtpl->parse('main');
		$xtpl->out('main');
	}
	
	
	// ----------------------------------------------------------------------
	// changeZones
	//
	// 
	// ----------------------------------------------------------------------
	function changeZones()
	{
		connect_db();
	
		$countries = $_REQUEST['country'];
		$zone = $_REQUEST['zone'];
		
		if (is_array($countries) && is_numeric($zone)) {
			foreach($countries as $country) {
				mysql_query("update countries set cou_zone = $zone where cou_id = '$country'");
			}
		}
	}
	
	
	// ----------------------------------------------------------------------
	// showZones
	//
	// 
	// ----------------------------------------------------------------------
	function showZones()
	{
		$xtpl = new XTemplate('show_zones.xtpl', '../xtpl/setup');
		connect_db();				
		
		$qry = mysql_query("select zon_name, zon_fixed_charge, zon_price_per_kilo, count(*) country_count from postal_zones, countries where zon_id = cou_zone group by zon_name, zon_fixed_charge, zon_price_per_kilo");
		while ($row = mysql_fetch_array($qry)) {
			$xtpl->assign($row);
			$xtpl->parse('main.zone');
		}	
		
		$xtpl->parse('main');
		$xtpl->out('main');
	}



}    
    
	
?>