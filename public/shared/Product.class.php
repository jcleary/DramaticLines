<?php
// --------------------------------------------------------------------------
// product.class.php
// --------------------------------------------------------------------------
// this is a parent class for all products

class Product {

    var $ProductCode;
    var $ProductCost;
    var $Description;
    var $ShippingCost;
    var $Weight;
    var $Qty = 1;

    var $_Record; // records the record from the database


    // ----------------------------------------------------------------------
    // Product
    //
    // Constructor
    // ----------------------------------------------------------------------
    function Product($ProductCode)
    {
        connect_db();

        $this->ProductCode = $ProductCode;

        $qry = mysql_query('select * from titles t, categories c where title_id='.$ProductCode.' and t.cat_id = c.cat_id');

        if ($Record = mysql_fetch_array($qry)) {
            $this->_Record      = $Record;

            $this->_Record['description'] = str_replace('{IMAGE_URL}', IMAGE_URL, $this->_Record['description']);

            $this->ProductCode  = $ProductCode;
            $this->ProductCost  = $Record['price'];
            $this->ShippingCost = $Record['p_and_p'];
            $this->Description  = $Record['title'];
            $this->Weight       = $Record['weight'];
        }
    }


    // ----------------------------------------------------------------------
    // ShowDetails
    //
    // Show the details of the product (book)
    // ----------------------------------------------------------------------
    function ShowDetails()
    {
        global $Basket;

        $xtpl = new XTemplate('book_title.xtpl', TEMPLATES);

        $xtpl->assign($this->_Record);

        if ($this->_Record['isbn']) {
            $xtpl->parse('main.isbn');
        }

        if ($this->_Record['author']) {
            $xtpl->parse('main.author');
        }

        $linkedItems = false;
        $qry = mysql_query("select * from titles, linked_items where from_id = " . $this->ProductCode . " and to_id = title_id");
        while ($row = mysql_fetch_assoc($qry)) {
            $xtpl->assign('linked', $row);
            $xtpl->parse('main.linked_items.item');
            $linkedItems = true;
        }
        if ($linkedItems) {
            $xtpl->parse('main.linked_items');
        }

        if ($this->_Record['perf_licences']) {
            $xtpl->parse('main.licences');
        }

        $xtpl->assign('TIME', TIME);
        $Basket->ParseBasketSummary($xtpl);

        $xtpl->parse('main');
        $xtpl->out('main');
    }



    // ----------------------------------------------------------------------
    // ShowEdit
    //
    // Show the edit page
    // ----------------------------------------------------------------------
    function ShowEdit()
    {
        $xtpl = new XTemplate('edit_book_title.xtpl', SETUP_TEMPLATES);

        $xtpl->assign($this->_Record);

        $xtpl->parse('main');
        $xtpl->out('main');
    }



    // ----------------------------------------------------------------------
    // Update
    //
    // Update the product details from the form fields
    // ----------------------------------------------------------------------
    function Update()
    {
        global $HTTP_POST_FILES;

        $UpdateFields = array('ord', 'top_order', 'title', 'sub_title', 'isbn', 'author', 'description', 'price', 'p_and_p', 'weight');

        $UpdateArray = array();
        foreach($UpdateFields as $Field) {
            $UpdateArray[] = $Field . " = '" . $_REQUEST[$Field] . "'";
        }

        $sql = 'update titles set ' . implode($UpdateArray, ',');
        $sql .= ' where title_id = ' .$this->ProductCode;


        if (strlen($HTTP_POST_FILES['book_cover']['name']) > 0) {
            upload_image('book_cover', BOOK_COVER_IMAGES, 150, 200);
            $Cover = $HTTP_POST_FILES['book_cover']['name'];
            mysql_query("update titles set cover = '$Cover' where title_id=".$this->ProductCode);
        }

        if (strlen($HTTP_POST_FILES['mini_book_cover']['name']) > 0) {
            upload_image('mini_book_cover', MINI_BOOK_COVER_IMAGES, 50, 50);
            $Cover = $HTTP_POST_FILES['mini_book_cover']['name'];
            mysql_query("update titles set button_up = '$Cover' where title_id=".$this->ProductCode);
        }

        connect_db();
        mysql_query($sql);
    }

}





?>