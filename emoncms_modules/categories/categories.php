<?php
  /*
   All Emoncms code is released under the GNU General Public License v3.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */
  class categories {

    //-------------------------------------------------------------------
    // 1) Direct according to path argument 1
    //-------------------------------------------------------------------
    function menu() {
      switch ($GLOBALS['args'][1]){
        case "all" :
          return $this->categories_viewlist($this->load_category_list()); //Display a list of categories
        break;
  
        case "user" :
          return $this->categories_view_user_list_pie($this->get_user_categories()); //Display a list of categories
        break;

        case "setup" :
          return $this->setup();
        break;

        default :
        //----------------------------------------------------------------------------------
        // CHECK IF IT'S A CATEGORY NAME AND DISPLAY RESPECTIVE PAGE
        //----------------------------------------------------------------------------------
        $name = $GLOBALS['args'][1];
        $query = "SELECT id FROM categories WHERE category='$name'"; // Check if category exists
        $result = db_query($query); $row = mysql_fetch_array($result);

        if ($row) {
          $catid = $row['id'];					// Get category id from query result
          $userid = $_SESSION['userid'];			// Get user id from propeties

          $catfile = "modules/categories/categories/$name/$name.php";		// Load the specific category file
          if (file_exists($catfile)) {
      
            include_once $catfile;

            // Check if category has been used with this user
            $result = db_query("SELECT * FROM cat_relation WHERE catid='$catid' AND userid='$userid'");
            if (mysql_fetch_array($result)) $relation = 1; else $relation = 0;

            $function = $name . "_view";
            $variables = $function($catid, $relation);
          }
        } 
        else 
        {
          $variables['content'] = "O dear.. this category does not exist! Terribly sorry..";
        }
          return $variables;
          break;
       }
    }

    //-------------------------------------------------------------------
    // Page: Setup
    //-------------------------------------------------------------------    
    function setup()
    {
      $variables['title'] = "Category Setup";
      $out = '';

      db_query(
      "CREATE TABLE categories
      (
        id int NOT NULL AUTO_INCREMENT, 
        PRIMARY KEY(id),
        category text,
        name text,
        description text,
        kwhd float
      )");

      $out .= "categories created<br/>";

      db_query(
      "CREATE TABLE cat_relation
      (
        userid int,
        catid int,
        kwhd float
      )");

      $out .= "cat_relation created<br/>";

      $this->categories_scan();

      $variables['content'] = $out;
      return $variables;
    }

    //-------------------------------------------------------------------------------------------
    // Scan for new categories and add to database
    //-------------------------------------------------------------------------------------------
    function categories_scan() {
        $out = '<br/>';

        $path = "modules/categories/categories";     // The category module folder path
        $results = scandir($path);    // Scan for category directories

        $new_category_flag = 0;    // Toggled to 1 if new category is found

        foreach ($results as $row) {    // For all directories:
            if (is_dir($path . '/' . $row) && ($row != '..') && ($row != '.')) {
                $lines = file($path . '/' . $row . '/' . $row . '.info'); // Open Catagory description file

                $ex_line = explode('=', $lines[0]);  // Isolate category
                $category = substr($ex_line[1], 1, -1);  // Remove first and last blanck space

                $ex_line = explode('=', $lines[1]);  // Isolate catagory name
                $name = substr($ex_line[1], 1, -1);  // Remove first and last blanck space

                $ex_line = explode('=', $lines[2]);  // Isolate catagory description
                $description = substr($ex_line[1], 1, -1); // Remove first and last blanck space

                //$out .= $category." ".$name." ".$description."<br/>";
                //if catagory does not exist in database catagory list then add catagory to database catagory list

                $query = "SELECT * FROM categories WHERE `category` = '$category'"; // Check if category exists
                $result = db_query($query);
                $cat = mysql_fetch_array($result);

                if (!$cat) {
                    $out .= "Adding category: " . $name . "<br/>";
                    $query = "INSERT INTO categories (category,name,description) VALUES ('$category','$name','$description')"; // Add new category
                    $result = db_query($query);

                    $script = "modules/categories/categories/" . $category . "/" . $category . ".php";
                    if (is_file($script))
                    {
                      include_once($script);
                      $setupfunction = $category."_setup";
                      $setupfunction();
                    }
                }
            }
        }
        return $out;
    }

    //----------------------------------------------------------------
    // Load category list from database
    //----------------------------------------------------------------
    function load_category_list() {

        $result = db_query("SELECT * FROM categories");

        $categories = array();
        while ($row = mysql_fetch_array($result)) {
            $categories[] = array(
                'id' => $row['id'],
                'category' => $row['category'],
                'name' => $row['name'],
                'description' => $row['description'],
                'kwhd' => $row['kwhd'],
            );
        }
        return $categories;
    }

    //----------------------------------------------------------------
    // Display a list of categories
    //----------------------------------------------------------------
    function categories_viewlist($categories) {

        $out = "<div class='lightbox'>";
        $out .= 'View and select the categories you wish to use:<br/><br/>';
        $out .= "<table class='catlist'><tr><th>Selected</th><th>Category</th></tr>";
        $i = 0;
        foreach ($categories as $category) {
            $i++;
            $out .= "<tr class='d" . ($i & 1) . "' >";
            $out .= "<td><input type=checkbox name='catselect'></td>";

            $out .= "<td><a href='" . $GLOBALS['systempath'] . "categories/" . $category['category'] . "'>" . $category['name'] . "</a><br/>" . $category['description'] . "</td></tr>";
        }
        $out .="</table></div>";


        $variables['title'] = "Category List";
        $variables['content'] = $out;
        return $variables;
    }

  //----------------------------------------------------------------
  // User: get user categories
  //----------------------------------------------------------------
  function get_user_categories()
  {
    $userid = $_SESSION['userid'];
    $query = "SELECT * FROM cat_relation WHERE `userid`='$userid'";	
    $result = db_query($query);

    $categories = array();
    while($row = mysql_fetch_array($result))             
    {
      $catid = $row['catid'];

      $query = "SELECT * FROM categories WHERE `id` = '$catid'";	
      $cat_result = db_query($query);
      $cat_row = mysql_fetch_array($cat_result);

      $categories[] = array(
      'id' => $cat_row['id'],
      'category' => $cat_row['category'],
      'name' => $cat_row['name'],
      'description' => $cat_row['description'],
      'kwhd' => $row['kwhd'],
      );
    } 

    return $categories;
  }

  //----------------------------------------------------------------
  // User Categories list page vith energy pie
  //----------------------------------------------------------------
  function categories_view_user_list_pie($categories)
  {
    $path = $GLOBALS['systempath'];

    $kwhtotal = 0;
    $data = array();
    if (isset($categories))
    {
      foreach ($categories as $category)
      {
        $data[] = array($category['name'],$category['kwhd']);
        $kwhtotal += $category['kwhd'];
      }
    }

   // $out = "<div class='lightbox' style='margin-bottom:20px;'>";

    //---------------------------------------------------------------------------------------------------
    // Display the energy pie
    //---------------------------------------------------------------------------------------------------
    $out = '
    <script language="javascript" type="text/javascript" src="'.$path.'flot/jquery.js"></script>
    <script language="javascript" type="text/javascript" src="'.$path.'flot/jquery.flot.js"></script>
    <script language="javascript" type="text/javascript" src="'.$path.'flot/jquery.flot.pie.js"></script>
    <script language="javascript" type="text/javascript" src="'.$path.'modules/categories/energyPie.js"></script>

    <div style="float:left; width:300px;"><i>Hover your mouse over the pie chart</i></div>
      <div class="lightbox" style="width:200px; padding:10px; float:right;">
      <a href="'.$path.'categories/all">Add Category</a> | <a href="'.$path.'feed/view">Setup a feed</a> 
      </div>

    <div style="height:380px">
    <div id="energypie" style="width: 480px; height: 350px; float:left; margin-top:20px;"></div>
    <br/><br/>
    <div id="total" style="font-size:38px; margin-top:60px;"><b>Total:</b><br/>'.$kwhtotal.' kWh/d</div><br/>
    <div id="slice" style="font-size:38px;">---</div>
    </div>
    <script type="text/javascript">
    $(document).ready(function(){
      var data2 = '.json_encode($data).';
      energy_pie(data2);
    });
    </script>
   ';
    //---------------------------------------------------------------------------------------------------

    $out .= '
      <h2>Renewable options</h2>
      <p><i>Hover your mouse over a slice of energy use to see how you could provide this energy from the following renewable options</i>
      <div id="solarpv" class="lightbox" style="margin-bottom:20px;">
      Solar PV
      </div>
      <div id="mwwind" class="lightbox" style="margin-bottom:20px;">
      1MW wind turbine
      </div>
    ';

/*
    $out .= "<div class='lightbox'>";

    $out .= '<h3>Your categories</h3>'; 
    $out .= "<table class='catlist'><tr><th>Category</th><th>kWh/d</th></tr>";
    $i=0;

    if (isset($this->categories))
    {
      foreach ($this->categories as $category)
      {
        $i++;
        $out .= "<tr class='d".($i & 1)."' >";
        $out .= "<td><a href='".$path."categories/".$category['category']."'>".$category['name']."</a><br/>".$category['description']."</td>";
        $out .= "<td>".$category['kwhd']." kWh/d</td></tr>";
      }
    }

    $out .="</table>";

    $out .="<br/><a href='".$path."categories/all'>Add Category</a></div>";
*/

    $variables['title'] = "My Energy profile";

    $variables['content'] = $out;
    return $variables;
  }

}

?>
