<?php
// **************************************************************************
// LinkedItems.class.php
//
// class to manage linked items
// **************************************************************************



include_once(SHARED . 'Base.class.php');



class LinkedItems extends Base 
{

    // ----------------------------------------------------------------------
    // showJoinList
    //
    // shows the "join mailing list" page
    // ----------------------------------------------------------------------
    function showLinkedItems()
    {
        $xtpl = new XTemplate('linked_items.xtpl', SETUP_TEMPLATES);
        
        $qry1 = mysql_query("select t1.title from_title, t2.title to_title, from_id, to_id from linked_items li, titles t1, titles t2 where li.from_id = t1.title_id and li.to_id = t2.title_id order by from_title, to_title");
        
        while ($row1 = mysql_fetch_assoc($qry1)) {
            $xtpl->assign($row1);
            $xtpl->parse('main.link');
        }
        
        
        $qry2 = mysql_query("select title, title_id from titles order by title");
        
        while ($row2 = mysql_fetch_assoc($qry2)) {            
            $xtpl->assign($row2);
            $xtpl->parse('main.from_option');
            $xtpl->parse('main.to_option');
        }
                    
        
        $xtpl->parse('main');
        echo $xtpl->text('main');        
    }
    
    
    
    // ----------------------------------------------------------------------
    // addLink
    //
    // adds a link
    // ----------------------------------------------------------------------
    function addLink($fromId, $toId)
    {
        mysql_query("insert into linked_items (from_id, to_id) values ($fromId, $toId)");        
    }


    
    // ----------------------------------------------------------------------
    // deleteLink
    //
    // deletes a link
    // ----------------------------------------------------------------------
    function deleteLink($fromId, $toId)
    {
        mysql_query("delete from linked_items where from_id = $fromId and to_id = $toId");        
    }


    

}

?>