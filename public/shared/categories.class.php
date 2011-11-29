<?php
// --------------------------------------------------------------------------
// categories.class.php
// --------------------------------------------------------------------------
// this class deals with product categories

class Categories {


    // ----------------------------------------------------------------------
    // ShowDetails
    //
    // Shows the categories
    // ----------------------------------------------------------------------
    
    function showDetails($FrontEnd = true)
    {
        connect_db();

        if (!$FrontEnd) {
        	return $this->showDetailsBackend();
        }
        
        $xtpl = new XTemplate('book_categories_midway.xtpl', TEMPLATES);
        $sql = 'select * from categories order by cat_order';
        $sql = 'select * from categories where cat_order > 0 order by cat_order';
        
        $maxBooksPerRow = 2;
        $column = 1;
        $qry = mysql_query('select * from titles where top_order > 0 order by top_order');
        
        while($row = mysql_fetch_array($qry)) {
            $xtpl->assign($row);

            if ($column == 1) {
                $xtpl->parse('main.bookRow.book.spacer');
            }

            $xtpl->parse('main.bookRow.book');

            if ($column == $maxBooksPerRow) {
                $xtpl->parse('main.bookRow');
                $column = 0; // soon to be increment to 1 below
            }

            $column ++;
        }


        // thid deals with odd numbers of books
        if ($column !== 1) {
            $xtpl->parse('main.bookRow.dummy_book');
            $xtpl->parse('main.bookRow');
        }

        $x = 0;

        $qry = mysql_query($sql);
        echo mysql_error();
        while ($row = mysql_fetch_array($qry)) {
            foreach($row as $field => $value) {
                $xtpl->assign($field, $value);
            }

            $cat_id = $row['cat_id'];

            $xtpl->parse('main.categoryRow.category.title');

            if ($x == 0) {

                $xtpl->parse('main.categoryRow.category.spacer');
            }

            $xtpl->parse('main.categoryRow.category');

            if ($x == 1) {
                $xtpl->parse('main.categoryRow');
                $x = 0;
            } else {
                $x++;
            }
        }

        global $Basket;
        if (is_object($Basket)) {
           $Basket->ParseBasketSummary($xtpl);
        }

        $xtpl->parse('main');
        $xtpl->out('main');
    }

    // ----------------------------------------------------------------------
    // ShowCategories
    //
    // Shows the books in a category
    // ----------------------------------------------------------------------
    function ShowCategory()
    {
        connect_db();

        $xtpl = new XTemplate('book_category.xtpl', TEMPLATES);

        $catId = $_REQUEST['cat_id'];

        // get the category information
        $qry = mysql_query("select * from categories where cat_id = $catId");
        if ($row = mysql_fetch_array($qry)) {
            $xtpl->assign($row);
        }


        $qry = mysql_query("select * from titles where cat_id = $catId");

        while($row = mysql_fetch_array($qry)) {
            $xtpl->assign($row);
            $xtpl->parse('main.book');
        }

        global $Basket;
        if (is_object($Basket)) {
           $Basket->ParseBasketSummary($xtpl);
        }

        $xtpl->parse('main');
        $xtpl->out('main');
    }

    // ----------------------------------------------------------------------
    // showGiftCategories
    //
    // shows all the categories that are part of the gift category
    // ----------------------------------------------------------------------
    function showGiftCategories()

    {
        connect_db();

        $xtpl = new XTemplate('GiftCategories.xtpl', TEMPLATES);

        $req = new Request();

        $catId = $req->get('catId');

        // get the category information
        $qry = mysql_query("select * from categories where cat_parent_id = $catId and cat_enabled=1");

        while($row = mysql_fetch_array($qry)) {
            $xtpl->assign($row);
            $xtpl->parse('main.category');
        }

        global $Basket;
        if (is_object($Basket)) {
           $Basket->ParseBasketSummary($xtpl);
        }

        $xtpl->parse('main');
        $xtpl->out('main');

    }

    // ----------------------------------------------------------------------
    // showGiftCategoryProducts
    //
    // show all the products in a gift category
    // ----------------------------------------------------------------------
    function showGiftCategoryProducts($catId)

    {
        connect_db();

        $qry = mysql_query("select cat_name, cat_subtitle, cat_template from categories where cat_id = $catId");
        $row = mysql_fetch_array($qry);

        $template = $row['cat_template'];

        if (!$template) {
            $template = 'GiftCategory.xtpl';
        }

        $xtpl = new XTemplate($template, TEMPLATES);

        $xtpl->assign('categoryName',     $row['cat_name']);
        $xtpl->assign('categorySubtitle', $row['cat_subtitle']);

        $qry = mysql_query("select title_id, title, button_up, price from titles where cat_id = $catId order by ord");

        $side = 'Left';

        $xtpl->assign('catId', $catId);

        while($row = mysql_fetch_array($qry)) {
            $xtpl->assign($row);
            $xtpl->parse('main.row.product' . $side);

            if ($side == 'Left') {
                $side = 'Right';
            } else {
                $xtpl->parse('main.row');
                $side = 'Left';
            }
        }

        if ($side == 'Right') {
            $xtpl->parse('main.row');
        }

        global $Basket;
        if (is_object($Basket)) {
           $Basket->ParseBasketSummary($xtpl);
        }

        $xtpl->parse('main');
        $xtpl->out('main');

    }
    
    function showDetailsBackend()
    {
        $xtpl = new XTemplate('book_categories.xtpl', SETUP_TEMPLATES);
    	$sql = 'select * from categories where cat_order > 0 order by cat_order';
        
        $qry = mysql_query($sql);
        
        while($row = mysql_fetch_assoc($qry)) {
            $xtpl->assign($row);            
            $this->assignTitles($xtpl, $row['cat_id']);            
            $xtpl->parse('main.category');
        }
                
        $xtpl->parse('main');
        $xtpl->out('main');
        echo 1121;
    	
    }
    
    function assignTitles($xtpl, $catId)
    {
        $qry = mysql_query("select title_id, title, button_up, price, ord from titles where cat_id = $catId order by ord");
    	    
        while($row = mysql_fetch_assoc($qry)) {
            $xtpl->assign($row);
            $xtpl->parse('main.category.title');
        }        
    }





}





?>