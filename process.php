<?PHP
 

error_reporting(-1);
ini_set("display_errors", 1);

$db = mysql_connect("YOUR_DB_ENDPOINT", "YOUR_DB_USERNAME", "YOUR_DB_PWD");
mysql_select_db("YOUR_DB_NAME", $db);

if(isset($_POST["operation"])!=FALSE) $_GET=$_POST;

$returnString="";

switch($_GET["operation"])
{
	//GENERATES VALUES FOR ADVANCED/PROFICIENT/BASIC ETC BAR CHART
	case "getScores":
		
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		$selectedGroup=$results;
		
		if($selectedGroup["Number".$_GET["subject"]] < 10 )
		{
			$returnString.="$('#tooFew').fadeIn('fast'); var noResults=1; $('#results').fadeOut('fast');";
		}
		else
		{
			//kill "too few" alert, if it exists
			$returnString.="$('#tooFew').fadeOut('fast'); var noResults=0;";
		
			//change total number of students
			$returnString.="$(\"#barReadout\").html(\"<h1 style='font-size:45pt;' id='totalStudents'>".$results["Number".$_GET["subject"]]."</h1><h4 style='position:relative; top:10px'>grade ".$results["grade"]." ".$results["group"]." students (".$results["Number".$_GET["subject"]]." total)</h4>\");";
			
			$returnString.="$('#totalNumber').html('".$results["Number".$_GET["subject"]]."');";
			
			//update bars
			$returnString.="$(\"#advancedBar\").attr('score', '".round($results["Advanced".$_GET["subject"]],0)."');";
			$returnString.="$(\"#advancedBar\").css('width', '".floatval($results["Advanced".$_GET["subject"]])."%');";
			$returnString.="$(\"#advancedBar .percentageNumber\").html('".round($results["Advanced".$_GET["subject"]],0)."%');";
			$returnString.="$(\"#proficientBar\").attr('score', '".round($results["Proficient".$_GET["subject"]],0)."');";
			$returnString.="$(\"#proficientBar\").css('width', '".floatval($results["Proficient".$_GET["subject"]])."%');";
			$returnString.="$(\"#proficientBar .percentageNumber\").html('".round($results["Proficient".$_GET["subject"]],0)."%');";
			$returnString.="$(\"#basicBar\").attr('score', '".round($results["Basic".$_GET["subject"]],0)."');";
			$returnString.="$(\"#basicBar\").css('width', '".floatval($results["Basic".$_GET["subject"]])."%');";
			$returnString.="$(\"#basicBar .percentageNumber\").html('".round($results["Basic".$_GET["subject"]],0)."%');";
			$returnString.="$(\"#belowBasicBar\").attr('score', '".round($results["BelowBasic".$_GET["subject"]],0)."');";
			$returnString.="$(\"#belowBasicBar\").css('width', '".(floatval($results["BelowBasic".$_GET["subject"]])-.1)."%');";
			$returnString.="$(\"#belowBasicBar .percentageNumber\").html('".round($results["BelowBasic".$_GET["subject"]],0)."%');";
			
			//update percentage on load
			$returnString.="$(\"#percentage h1\").html('".(round($results["Advanced".$_GET["subject"]],0) + round($results["Proficient".$_GET["subject"]],0))."%');";
			$returnString.="$(\"#percentage h4\").html('Scored proficient or higher');";
			
			//query benchmark group
			$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = 'All Students'");
			$results=mysql_fetch_assoc($query);
			
			//update benchmark bars
			$returnString.="$(\"#advancedBenchmark\").css('width', '".$results["Advanced".$_GET["subject"]]."%');";
			$returnString.="$(\"#proficientBenchmark\").css('width', '".$results["Proficient".$_GET["subject"]]."%');";
			$returnString.="$(\"#basicBenchmark\").css('width', '".$results["Basic".$_GET["subject"]]."%');";
			$returnString.="$(\"#belowBasicBenchmark\").css('width', '".(floatval($results["BelowBasic".$_GET["subject"]])-.1)."%');";
		}
	
	break;
	
	case "getInSchoolRanks":
		
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		$selectedGroup=$results;//get demographic of interest
		
				
		//get overall average score
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = 'All Students'");
		$results=mysql_fetch_assoc($query);
		$baseline=$results;
		
		
		//compare to demographics in same school
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' AND Number".$_GET["subject"]." >=10 ORDER BY ProficientAndAbove".$_GET["subject"]." DESC");
		$results=mysql_fetch_assoc($query);
		
		$returnString.="<table class='table table-hover'><thead><th>Demographic</th><th>Proficient or above</th><th>Percentage points deviation from demographic</th></thead>";
 
		$i=1;
			
		while($results != FALSE)
		{
			if($results["group"] == $_GET["group"]) $returnString .= "<tr class='warning'><td><strong>".$results["group"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</strong></td><td>-</td></tr>";
			else
			{
				$returnString .= "<tr  ".$results["group"].": ".$results["ProficientAndAbove".$_GET["subject"]];
				
				
				
				//build on variance string?
				$variance=round($results["ProficientAndAbove".$_GET["subject"]]-$selectedGroup["ProficientAndAbove".$_GET["subject"]],1);
				if($variance < 0 AND $variance > -100) $returnString .= "<tr class='error'><td>".$results["group"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</td><td>".$variance."</td></tr>";
				if($variance > 0) $returnString .= "<tr class='success'><td>".$results["group"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</td><td>".$variance."</td></tr>"; 
			
			}
										
			$results=mysql_fetch_assoc($query);
			
		}
		
		$returnString.="</table>";
		
		
		
	
	break;
	
	case "getInDistrictRanks": //compare to other schools in the district
		
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		$selectedGroup=$results;//get demographic of interest
		
		//compare to demographics districtwide
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."' AND district = '".$_GET["district"]."' AND Number".$_GET["subject"]." >=10 ORDER BY ProficientAndAbove".$_GET["subject"]." DESC");
		$results=mysql_fetch_assoc($query);
		
		$returnString.="<table class='table table-hover'><thead><th>School</th><th>Proficient or above</th><th>Percentage point deviation from your school</th></thead>";
 
		$i=1;
			
		while($results != FALSE)
		{
			if($results["school"] == $_GET["school"]) $returnString .= "<tr class='warning'><td><strong>".$results["school"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</strong></td><td>-</td></tr>";
			else
			{
				$returnString .= "<tr  ".$results["group"].": ".$results["ProficientAndAbove".$_GET["subject"]];
				
				
				
				//build on variance string?
				$variance=round($results["ProficientAndAbove".$_GET["subject"]]-$selectedGroup["ProficientAndAbove".$_GET["subject"]],1);
				if($variance < 0 AND $variance > -100) $returnString .= "<tr class='error'><td>".$results["school"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</td><td>".$variance."</td></tr>";
				if($variance > 0) $returnString .= "<tr class='success'><td>".$results["school"]."</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."%</td><td>".$variance."</td></tr>"; 
			
			}
										
			$results=mysql_fetch_assoc($query);
			
		}
		
		$returnString.="</table>";
		
	break; 
	
	case "getStateRanks":
	
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		$selectedGroup=$results;//get demographic of interest
		
		//compare to demographics statewide
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."' AND Number".$_GET["subject"]." >=10 ORDER BY ProficientAndAbove".$_GET["subject"]." DESC");
		$results=mysql_fetch_assoc($query);
		
		
		//load values into an array
		$stateScores=array();
		$i=0;
		
		while($results!=FALSE)	
		{
			$stateScores[]=$results; 
			if($results["school"]==$_GET["school"] AND $results["district"]==$_GET["district"]) $key=$i;
			$results=mysql_fetch_assoc($query);
			$i++;
		}
		
		//find position of selected school
		$tied=1;
		$offset=0;
		if($key != 0){while($stateScores[$key]["ProficientAndAbove".$_GET["subject"]] == $stateScores[$key-$tied]["ProficientAndAbove".$_GET["subject"]]) { if($key-$tied-1 < 0){ break;} $offset++; $tied++; }} //again, gotta check to see if there's tied schoools. this sets the percentage and ranking correctly.
		$returnString.="$('#stateRank').html('".number_format($key+1-$offset)."/".number_format($i)."');";
		$returnString.="$('#percentile').html('".round((1-($key+1-$offset)/$i)*100,1)." percentile');";
		
		$tableString="<thead><tr><th>Rank</th><th>School (District)</th><th>Proficient or higher</th></tr></thead>";
		
		$tied=0;
		for($i=$key-3; $i<=$key+3; $i++)
		{
			if($i < 0 or isset($stateScores[$i]) == FALSE) continue;
			if($i>0){if($stateScores[$i]["ProficientAndAbove".$_GET["subject"]] == $stateScores[$i-1]["ProficientAndAbove".$_GET["subject"]]) {$tied+=1;} else {$tied=0;}} //keep tied scores at the same ranking
			if($i==$key) $color="warning"; else if($stateScores[$i]["ProficientAndAbove".$_GET["subject"]] >= $selectedGroup["ProficientAndAbove".$_GET["subject"]]) $color="success"; else $color="error";
			$tableString.="<tr class=\"".$color."\"><td>".number_format($i+1-$tied)."</td><td>".$stateScores[$i]["school"]."(".$stateScores[$i]["district"].")</td><td>".$stateScores[$i]["ProficientAndAbove".$_GET["subject"]]."</td></tr>";
			
			//make sure this 5 surrounding thing doens't break the array max or min
		}
		
		$tableString.="</table>";
		$returnString.="$('#peers').html('".$tableString."')";
		
	break;
	
	case "getYearOverYear":
		$grades = array(3, 4, 5, 6, 7, 8, 11);
		$flotData="";
		
		for($i=0; $i<=sizeof($grades)-1; $i++)
		{
			$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$grades[$i]."' and `group` = '".$_GET["group"]."' and district = '".$_GET["district"]."'");
			$results=mysql_fetch_assoc($query);
			
			$students=0;
			$scores=0;
			
			while($results != FALSE)
			{
				$students+=$results["Number".$_GET["subject"]];
				$scores+=$results["ProficientAndAbove".$_GET["subject"]]*$results["Number".$_GET["subject"]];
				$results=mysql_fetch_assoc($query);
			}
			
			$flotData.="[".$grades[$i].",".$scores/$students."], "; 
			
		}
		
		$averageFlotData="";
		
		for($i=0; $i<=sizeof($grades)-1; $i++)
		{
			$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$grades[$i]."' and `group` = 'All Students' and district = '".$_GET["district"]."'");
			$results=mysql_fetch_assoc($query);
			
			$students=0;
			$scores=0;
			
			while($results != FALSE)
			{
				$students+=$results["Number".$_GET["subject"]];
				$scores+=$results["ProficientAndAbove".$_GET["subject"]]*$results["Number".$_GET["subject"]];
				$results=mysql_fetch_assoc($query);
			}
			
			$averageFlotData.="[".$grades[$i].",".$scores/$students."], "; 
			
		}
			
		$flotData=substr($flotData, 0, (sizeof($flotData)-3));
		$averageFlotData=substr($averageFlotData, 0, (sizeof($averageFlotData)-3));
		$returnString.="var plot = $.plot($('#timeChart'), [ {label: '".$_GET["group"]."', data:[ ".$flotData." ]}, {label: 'Average', data:[ ".$averageFlotData." ]}], {series:{hoverable:true, points:{ show:true }, lines: { show:true } }, xaxis:{ ticks: [2,3,4,5,6,7,8,11] }, yaxis:{min:0, max:100} });";
		
	break;
	
	case "getDistrictSuggestion":
		$query=mysql_query("SELECT DISTINCT district FROM schoolScores2012 WHERE district LIKE '".$_GET["district"]."%'");
		$results=mysql_fetch_assoc($query);
		
		if($results != FALSE) $returnString = "Suggestions: <span class='suggestion'>".$results["district"]."</span>";
		
		for($i=0; $i<=2; $i++)
		{
			$results=mysql_fetch_assoc($query);
			if ($results != FALSE) $returnString.= ", <span class='suggestion'>".$results["district"]."</span>";
		}
				
	break;
	
	case "getSchoolSuggestion":
		$query=mysql_query("SELECT DISTINCT school FROM schoolScores2012 WHERE school LIKE '".$_GET["school"]."%' AND district = '".$_GET["district"]."' and grade = '".$_GET["grade"]."'");
		$results=mysql_fetch_assoc($query);
		
		if($results != FALSE) $returnString = "Suggestions: <span class='suggestion'>".$results["school"]."</span>";
		
		for($i=0; $i<=2; $i++)
		{
			$results=mysql_fetch_assoc($query);
			if ($results != FALSE) $returnString.= ", <span class='suggestion'>".$results["school"]."</span>";
		}
				
	break;
	
	case "getAllSchools":
		$query=mysql_query("SELECT DISTINCT school FROM schoolScores2012 WHERE school LIKE '".$_GET["school"]."%' AND district = '".$_GET["district"]."' and grade = '".$_GET["grade"]."'");
		$results=mysql_fetch_assoc($query);
		
			if($results != FALSE) $returnString = "Suggestions: <span class='suggestion'>".$results["school"]."</span>";
		
		while($results != FALSE)
		{
			$results=mysql_fetch_assoc($query);
			if ($results != FALSE) $returnString.= ", <span class='suggestion'>".$results["school"]."</span>";
		}
	break;
	
	case "getSimilarSchools":
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		$selectedGroup=$results;//get demographic of interest
		
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE district = '".$_GET["district"]."' AND school = '".$_GET["school"]."' AND grade = '".$_GET["grade"]."' and `group` = 'All Students'");
		$results=mysql_fetch_assoc($query);
		$baseline=$results;//get demographic of total school population
	
		$i=0;
		$returnString.="<thead><th>School (District)<th>Proficient or higher</th><th>Total students (Difference from your school)</th><th>".ucfirst(formatGroup($_GET["group"]))." students (Difference from your school)</th></thead>";
		
		$groupQuery=mysql_query("
								SELECT * FROM schoolScores2012 
								WHERE 
								grade = '".$_GET["grade"]."' 
								AND `group` = '".$_GET["group"]."' 
								AND county = '".$selectedGroup["county"]."'
								AND ProficientAndAbove".$_GET["subject"]." > ".$selectedGroup["ProficientAndAbove".$_GET["subject"]]." 
								AND Number".$_GET["subject"]." <= ".($selectedGroup["Number".$_GET["subject"]]*1.1)." 
								AND Number".$_GET["subject"]." >= ".($selectedGroup["Number".$_GET["subject"]]*.9)."
								ORDER BY ProficientAndAbove".$_GET["subject"]." DESC");
		$groupResults=mysql_fetch_assoc($groupQuery);
			
		while($groupResults != FALSE)
		{
				
			$query=mysql_query("
								SELECT * FROM schoolScores2012 
								WHERE 
								school = '".$groupResults["school"]."'
								AND grade = '".$_GET["grade"]."' 
								AND `group` = 'All Students'
								AND Number".$_GET["subject"]." <= ".($baseline["Number".$_GET["subject"]]*1.3)." 
								AND Number".$_GET["subject"]." >= ".($baseline["Number".$_GET["subject"]]*.7));
			$results=mysql_fetch_assoc($query);
			
			
			
			if($results != FALSE ) 
			{
				$returnString.="<tr class='success'>
				`				<td>".$results["school"]." (".$results["district"].")</td>
								<td>".$groupResults["ProficientAndAbove".$_GET["subject"]]."</td>
								<td>".$results["Number".$_GET["subject"]]." (".($baseline["Number".$_GET["subject"]]-$results["Number".$_GET["subject"]]).")</td>
								<td>".$groupResults["Number".$_GET["subject"]]."(".($selectedGroup["Number".$_GET["subject"]]-$groupResults["Number".$_GET["subject"]]).")</td></tr>";
				$i++;
			}
			
			if($i==5) break;
			
			$groupResults=mysql_fetch_assoc($groupQuery);
		}
			
		if($i==0) $returnString="Your school has no comparable better option in your area.";
		
	break;
	
	case "getBestSchools":
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."' ORDER BY ProficientAndAbove".$_GET["subject"]." DESC");
		
		for($i=0; $i<=2+$_GET["offset"]; $i++)
		{
			$results=mysql_fetch_assoc($query);
			if($i >= $_GET["offset"]) $returnString.="<tr class='success'><td>".$results["school"]." (".$results["district"].")</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."</td>";
		}
		
	break;	
	
	case "getWorstSchools":
		//get demographic of interest
		$query=mysql_query("SELECT * FROM schoolScores2012 WHERE grade = '".$_GET["grade"]."' and `group` = '".$_GET["group"]."' AND Number".$_GET["subject"]." >=15 ORDER BY ProficientAndAbove".$_GET["subject"]." ASC");
		
		for($i=0; $i<=2+$_GET["offset"]; $i++)
		{
			$results=mysql_fetch_assoc($query);
			if($i >= $_GET["offset"]) $returnString.="<tr class='error'><td>".$results["school"]." (".$results["district"].")</td><td>".$results["ProficientAndAbove".$_GET["subject"]]."</td>";
		}
		
	break;	
	
	case "getContactInfo":
		$query=mysql_query("SELECT * FROM schoolScores2012 INNER JOIN schoolList on schoolScores2012.schoolID = schoolList.ID WHERE schoolScores2012.district = '".$_GET["district"]."' AND schoolScores2012.school = '".$_GET["school"]."' AND schoolScores2012.grade = '".$_GET["grade"]."' and schoolScores2012.`group` = '".$_GET["group"]."'");
		$results=mysql_fetch_assoc($query);
		if($results != FALSE) {
			$returnString.= "<h4>Superintendent</h4>";
			$returnString.=/*"	<address><strong>".$results["SuperFirstName"]." ".$results["SuperLastName"]."</strong><br />
									".*/$results["District"]."<br />
									".$results["DistrictStreet"]."<br />
									".$results["DistrictCity"].", ".$results["DistrictState"]."<br />
									".$results["DistrictZip"]."<br />
									<em>Phone</em>: ".formatPhone($results["SuperPhone"])."
								</address>
								<h4>Principal</h4>
								<address>"./*
									<Strong>".$results["PrincipalFirst"]." ".$results["PrincipalLast"]."</strong><br />
									".*/$results["School"]."<br />
									".$results["SchoolStreet"]."<br />
									".$results["SchoolCity"].", ".$results["DistrictState"]."<br />
									".$results["SchoolZip"]."<br />
									<em>Phone</em>: ".formatPhone($results["PrincipalPhone"])."
								</address>";
		}
		else $returnString.="No school contacts listed.";	
		
		
	
	break;
	
	default:
		echo "Nothing given, nothing received.";
	break;
}

echo $returnString;

function formatPhone($phone)
{
	return substr($phone, 0, 3)."-".substr($phone, 3, 3)."-".substr($phone, 6,4);
}

function formatGroup($group)
{
	switch($group)
	{
		case "All Students": return "all"; break;
		case "Male": return "male"; break;
		case "Female": return "female"; break;
		case "White": return "white"; break;
		case "Black": return "black"; break;
		case "Hispanic": return "Hispanic"; break;
		case "Asian": return "Asian"; break;
		case "Native American": return "Native American"; break;
		case "Multi-ethnic": return "multi-ethnic"; break;
		case "IEP": return "special needs"; break;
		case "Economically disadvantaged": return "economically disadvantaged"; break;
		case "ELL": return "English language learner"; break;
	}
}

?>