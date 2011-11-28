<?php
/* $Id: xtemplate.inc,v 1.4 2001/08/17 18:25:45 jeremy Exp $
// $Log: xtemplate.inc,v $
// Revision 1.4  2001/08/17 18:25:45  jeremy
// Sorted greedy matching regular expression in parse function preg_match_all line 166: added ? after .* when looking for comments
//
*/

//error_reporting(E_ALL);
class XTemplate {

    /*
    xtemplate class 0.3pre
    html generation with templates - fast & easy
    copyright (c) 2000-2001 Barnabás Debreceni [cranx@users.sourceforge.net]

    contributors:
    Ivar Smolin <okul@linux.ee> (14-march-2001)
    - made some code optimizations
    Bert Jandehoop <bert.jandehoop@users.info.wau.nl> (26-june-2001)
    - new feature to substitute template files by other templates
    - new method array_loop()

    !!! {FILE {VAR}} file variable interpolation may still be buggy !!!

    latest stable & CVS versions always available @
    http://sourceforge.net/projects/xtpl/

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU Lesser General Public License
    version 2.1 as published by the Free Software Foundation.

    This library is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details at
    http://www.gnu.org/copyleft/lgpl.html

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

    */

    /***[ variables ]***********************************************************/

    var $filecontents="";                               /* raw contents of template file */
    var $blocks=array();                                /* unparsed blocks */
    var $parsed_blocks=array();                 /* parsed blocks */
    var $preparsed_blocks=array();          /* preparsed blocks, for file includes */
    var $block_parse_order=array();         /* block parsing order for recursive parsing (sometimes reverse:) */
    var $sub_blocks=array();                        /* store sub-block names for fast resetting */
    var $VARS=array();                                  /* variables array */
    var $FILEVARS=array();                          /* file variables array */
    var $filevar_parent=array();                /* filevars' parent block */
    var $filecache=array();                         /* file caching */

    var $tpldir="";                     /* location of template files */
    var $FILES=null;                    /* file names lookup table */
    var $filename = '';

    // moved to setup method so uses the tag_start & end_delims
    var $file_delim='';//"/\{FILE\s*\"([^\"]+)\"\s*\}/m";  /* regexp for file includes */
    var $filevar_delim='';//"/\{FILE\s*\{([A-Za-z0-9\._]+?)\}\s*\}/m";  /* regexp for file includes */
    var $filevar_delim_nl='';//"/^\s*\{FILE\s*\{([A-Za-z0-9\._]+?)\}\s*\}\s*\n/m";  /* regexp for file includes w/ newlines */
    var $block_start_delim="<!-- ";         /* block start delimiter */
    var $block_end_delim="-->";                 /* block end delimiter */
    var $block_start_word="BEGIN:";         /* block start word */
    var $block_end_word="END:";                 /* block end word */

    /* this makes the delimiters look like: <!-- BEGIN: block_name --> if you use my syntax. */

    var $tag_start_delim = '{';
    var $tag_end_delim = '}';
    /* this makes the delimiters look like: {tagname} if you use my syntax. */

    var $NULL_STRING=array(""=>"");             /* null string for unassigned vars */
    var $NULL_BLOCK=array(""=>"");  /* null string for unassigned blocks */
    var $mainblock="main";
    var $ERROR="";
    var $AUTORESET=1;                                       /* auto-reset sub blocks */

    var $_ignoreMissingBlocks = true ;          // NW 17 oct 2002 - Set to FALSE to
    // generate errors if a non-existant blocks is referenced

    // JC 20/11/02 for echoing the template filename if in development
    var $_FileNameFullPath = "";
    var $OutputType = "HTML";

    /***[ constructor ]*********************************************************/


    function XTemplate ($file,$tpldir="",$files=null,$mainblock="main",$autosetup=true) {
        $this->tpldir = $tpldir;
        if (gettype($files)=="array")
        $this->FILES = $files;
        $this->mainblock=$mainblock;

        $this->filename = $file;

        // JC 20/11/02 for echoing the template filename if in development
        $this->_FileNameFullPath = realpath($file);

        if ($autosetup) {
            // setup the rest of the preprocess elements
            $this->setup();
        }
    }


    /***************************************************************************/
    /***[ public stuff ]********************************************************/
    /***************************************************************************/


    /***[ setup ]***************************************************************/
    /*
    setup - the elements that were previously in the constructor
    if the true is passed when called, it adds an outer main block to the file
    */

    function setup ($add_outer = false) {

        $this->tag_start_delim = preg_quote($this->tag_start_delim);
        $this->tag_end_delim = preg_quote($this->tag_end_delim);

        // Setup the file delimiters
        $this->file_delim="/" . $this->tag_start_delim . "FILE\s*\"([^\"]+)\"\s*" . $this->tag_end_delim . "/m";  /* regexp for file includes */
        $this->filevar_delim="/" . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._]+?)" . $this->tag_end_delim . "\s*" . $this->tag_end_delim . "/m";  /* regexp for file includes */
        $this->filevar_delim_nl="/^\s*" . $this->tag_start_delim . "FILE\s*" . $this->tag_start_delim . "([A-Za-z0-9\._]+?)" . $this->tag_end_delim . "\s*" . $this->tag_end_delim . "\s*\n/m";  /* regexp for file includes w/ newlines */

        $this->filecontents=$this->r_getfile($this->filename);    /* read in template file */

        if ($add_outer) {
            $this->_add_outer_block();
        }

        $this->blocks=$this->maketree($this->filecontents,"");  /* preprocess some stuff */
        $this->filevar_parent=$this->store_filevar_parents($this->blocks);
        $this->scan_globals();
    }

    /***[ add_outer_block ]*******************************************************/
    /*
    add an outer block delimiter set useful for rtfs etc - keeps them editable in word
    */

    function _add_outer_block () {
        $before = $this->block_start_delim . $this->block_start_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;
        $after = $this->block_start_delim . $this->block_end_word . ' ' . $this->mainblock . ' ' . $this->block_end_delim;

        $this->filecontents = $before . "\n" . $this->filecontents . "\n" . $after;
    }

    /***[ assign ]**************************************************************/
    /*
    assign a variable
    */

    function assign ($name,$val="") {
        if (gettype($name)=="array")
        foreach ($name as $k=>$v)
        $this->VARS[$k]=$v;
        else
        $this->VARS[$name]=$val;
    }

    /***[ assign_file ]*********************************************************/
    /*
    assign a file variable
    */

    function assign_file ($name,$val="") {
        if (gettype($name)=="array")
        foreach ($name as $k=>$v)
        $this->assign_file_($k,$v);
        else
        $this->assign_file_($name,$val);
    }

    function assign_file_ ($name,$val) {
        if (isset($this->filevar_parent[$name])) {
            if ($val!="") {
                $val=$this->r_getfile($val);
                foreach($this->filevar_parent[$name] as $parent) {
                    if (isset($this->preparsed_blocks[$parent]) and !isset($this->FILEVARS[$name]))
                    $copy=$this->preparsed_blocks[$parent];
                    else if (isset($this->blocks[$parent]))
                    $copy=$this->blocks[$parent];
                    preg_match_all($this->filevar_delim,$copy,$res,PREG_SET_ORDER);
                    foreach ($res as $v) {
                        $copy=preg_replace("/".preg_quote($v[0])."/","$val",$copy);
                        $this->preparsed_blocks=array_merge($this->preparsed_blocks,$this->maketree($copy,$parent));
                        $this->filevar_parent=array_merge($this->filevar_parent,$this->store_filevar_parents($this->preparsed_blocks));
                    }
                }
            }
        }
        $this->FILEVARS[$name]=$val;
    }

    /***[ parse ]***************************************************************/
    /*
    parse a block
    */

    function parse ($bname) {
        if (isset($this->preparsed_blocks[$bname])) {
            $copy=$this->preparsed_blocks[$bname];
        }
        elseif (isset($this->blocks[$bname]))
        $copy=$this->blocks[$bname];

        // ------------------------------------------------------
        // NW : 17 Oct 2002. Added default of ignoreMissingBlocks
        //      to allow for generalised processing where some
        //      blocks may be removed from the HTML without the
        //      processing code needing to be altered.
        // ------------------------------------------------------
        // JRC: 3/1/2003 added set error to ignore missing functionality
        elseif ($this->_ignoreMissingBlocks) {
        $this->set_error ("parse: blockname [$bname] does not exist");
        return ;
        } else
        $this->set_error ("parse: blockname [$bname] does not exist");

        /* from there we should have no more {FILE } directives */
        if (!isset($copy)) die('Block: ' . $bname);
        $copy=preg_replace($this->filevar_delim_nl,"",$copy);

        /* find & replace variables+blocks */
        preg_match_all("/" . $this->tag_start_delim . "([A-Za-z0-9\._]+? ?#?.*?)" . $this->tag_end_delim. "/",$copy,$var_array);
        $var_array=$var_array[1];
        foreach ($var_array as $k=>$v) {
            $any_comments = explode("#", $v);
            $v = rtrim($any_comments[0]);
            if (sizeof($any_comments) > 1) {
                $comments = $any_comments[1];
            } else {
                $comments = '';
            }
            $sub=explode(".",$v);
            if ($sub[0]=="_BLOCK_") {
                unset($sub[0]);
                $bname2=implode(".",$sub);
                $var=isset($this->parsed_blocks[$bname2]) ? $this->parsed_blocks[$bname2] : null; // eliminates assign error in E_ALL reporting
                $nul=(!isset($this->NULL_BLOCK[$bname2])) ? $this->NULL_BLOCK[""] : $this->NULL_BLOCK[$bname2];
                if ($var=="") {
                    if ($nul=="") {
                        // -----------------------------------------------------------
                        // Removed requriement for blocks to be at the start of string
                        // -----------------------------------------------------------
//                      $copy=preg_replace("/^\s*\{".$v."\}\s*\n*/m","",$copy);
                        // Now blocks don't need to be at the beginning of a line,
                        //$copy=preg_replace("/\s*" . $this->tag_start_delim . $v . $this->tag_end_delim . "\s*\n*/m","",$copy);
                        $copy=preg_replace("/" . $this->tag_start_delim . $v . $this->tag_end_delim . "/m", "", $copy);
                    } else {
                        $copy=preg_replace("/" . $this->tag_start_delim . $v . $this->tag_end_delim . "/","$nul",$copy);
                    }
                } else {
                    $var=trim($var);
                    $copy=preg_replace("/" . $this->tag_start_delim . $v . $this->tag_end_delim . "/","$var",$copy);
                }
            } else {
                $var=$this->VARS;
                foreach ($sub as $v1) {
                    // NW 4 Oct 2002 - Added isset and is_array check to avoid NOTICE messages
                    // JC 17 Oct 2002 - Changed EMPTY to stlen=0
                    //                if (empty($var[$v1])) { // this line would think that zeros(0) were empty - which is not true
                    if (!isset($var[$v1]) || (!is_array($var[$v1]) && strlen($var[$v1])==0)) {
                        if (defined($v1)) { // Check for constant, when variable not assigned
                        $var[$v1] = constant($v1);
                        } else {
                            $var[$v1] = null;
                        }
                    }
                    $var=$var[$v1];
                }
                $nul=(!isset($this->NULL_STRING[$v])) ? ($this->NULL_STRING[""]) : ($this->NULL_STRING[$v]);
                $var=(!isset($var))?$nul:$var;
                if ($var=="") {
                    // -----------------------------------------------------------
                    // Removed requriement for blocks to be at the start of string
                    // -----------------------------------------------------------
//                    $copy=preg_replace("|^\s*\{".$v." ?#?".$comments."\}\s*\n|m","",$copy);
                    $copy=preg_replace("|\s*" . $this->tag_start_delim . $v . " ?#?" . $comments . $this->tag_end_delim . "\s*\n|m","",$copy);
                }
                $copy=preg_replace("|" . $this->tag_start_delim . $v . " ?#?" . $comments . $this->tag_end_delim . "|","$var",$copy);
            }
        }
        if (isset($this->parsed_blocks[$bname])) {
            $this->parsed_blocks[$bname].=$copy;
        } else {
            $this->parsed_blocks[$bname]=$copy;
        }

        /* reset sub-blocks */
        if ($this->AUTORESET && (!empty($this->sub_blocks[$bname]))) {
            reset($this->sub_blocks[$bname]);
            foreach ($this->sub_blocks[$bname] as $k=>$v)
            $this->reset($v);
        }
    }

    /***[ rparse ]**************************************************************/
    /*
    returns the parsed text for a block, including all sub-blocks.
    */

    function rparse($bname) {
        if (!empty($this->sub_blocks[$bname])) {
            reset($this->sub_blocks[$bname]);
            foreach ($this->sub_blocks[$bname] as $k=>$v)
            if (!empty($v))
            $this->rparse($v);
        }
        $this->parse($bname);
    }

    /***[ insert_loop ]*********************************************************/
    /*
    inserts a loop ( call assign & parse )
    */

    function insert_loop($bname,$var,$value="") {
        $this->assign($var,$value);
        $this->parse($bname);
    }

    /***[ array_loop ]*********************************************************/
    /*
    parses a block for every set of data in the values array
    */

    function array_loop($bname, $var, &$values)
    {
        if (gettype($values)=="array")
        {
            foreach($values as $v)
            {
                $this->assign($var, $v);
                $this->parse($bname);
            }
        }
    }

    /***[ text ]****************************************************************/
    /*
    returns the parsed text for a block
    */

    function text($bname) {
        // JC 20/11/02 moved from ::out()

        // don't show the template name for the header
        // as this prevents the doctype from being the first line
        // that confuses some browsers
        $isHeader = (basename(strtolower($this->_FileNameFullPath), '.xtpl') == 'header');

        if (!$isHeader && SYSTEM_TYPE == 'development' && $this->OutputType == "HTML") {
            $Text = "<!-- Template: " . $this->_FileNameFullPath . " -->\n";
        } else {
            $Text = "";
        }
        $Text .= $this->parsed_blocks[isset($bname) ? $bname :$this->mainblock];
        return $Text;
    }

    /***[ out ]*****************************************************************/
    /*
    prints the parsed text
    */

    function out ($bname) {
        $length=strlen($this->text($bname));
        //header("Content-Length: ".$length); // TODO: Comment this back in later

        // JC 20/11/02 echo the template filename if in development as
        // html comment
        // note 4.3.0 and ZE2 have new function debug_backtrace() that show a
        // function call list - it may be nice to dump that here too
        //if (SYSTEM_TYPE == 'development') {
        //    echo "<!-- Template: " . $this->FileNameFullPath . " -->\n";
        //}
        // moved to ::text() so parsing sub templates work

        echo $this->text($bname);
    }

    /***[ out_file ]*****************************************************************/
    /*
    prints the parsed text to a specified file
    */

    function out_file ($bname, $fname) {
        if (!empty($bname) && !empty($fname)) {
            $fp = fopen($fname, "w");
            fwrite($fp, $this->text($bname));
            fclose($fp);
        }
    }

    /***[ reset ]***************************************************************/
    /*
    resets the parsed text
    */

    function reset ($bname) {
        $this->parsed_blocks[$bname]="";
    }

    /***[ parsed ]**************************************************************/
    /*
    returns true if block was parsed, false if not
    */

    function parsed ($bname) {
        return (!empty($this->parsed_blocks[$bname]));
    }

    /***[ SetNullString ]*******************************************************/
    /*
    sets the string to replace in case the var was not assigned
    */

    function SetNullString($str,$varname="") {
        $this->NULL_STRING[$varname]=$str;
    }

    /***[ SetNullBlock ]********************************************************/
    /*
    sets the string to replace in case the block was not parsed
    */

    function SetNullBlock($str,$bname="") {
        $this->NULL_BLOCK[$bname]=$str;
    }

    /***[ set_autoreset ]*******************************************************/
    /*
    sets AUTORESET to 1. (default is 1)
    if set to 1, parse() automatically resets the parsed blocks' sub blocks
    (for multiple level blocks)
    */

    function set_autoreset() {
        $this->AUTORESET=1;
    }

    /***[ clear_autoreset ]*****************************************************/
    /*
    sets AUTORESET to 0. (default is 1)
    if set to 1, parse() automatically resets the parsed blocks' sub blocks
    (for multiple level blocks)
    */

    function clear_autoreset() {
        $this->AUTORESET=0;
    }

    /***[ scan_globals ]********************************************************/
    /*
    scans global variables
    */

    function scan_globals() {
        reset($GLOBALS);
        foreach ($GLOBALS as $k=>$v)
        $GLOB[$k]=$v;
        $this->assign("PHP",$GLOB); /* access global variables as {PHP.HTTP_HOST} in your template! */
    }

    /******

    WARNING
    PUBLIC FUNCTIONS BELOW THIS LINE DIDN'T GET TESTED

    ******/


    /***************************************************************************/
    /***[ private stuff ]*******************************************************/
    /***************************************************************************/

    /***[ __sleep]*********************************************************/
    // the serialise callback
    //
    // we can't allow an attempt to serialise this - its far too big and has
    // consequently caused loss of session data in some cases.
    // If you think you really want to do this the you are wrong!

    function __sleep()

    {
        trigger_error("Cannot serialise XTemplate", E_USER_ERROR);
        die();
    }

    /***[ maketree ]************************************************************/
    /*
    generates the array containing to-be-parsed stuff:
    $blocks["main"],$blocks["main.table"],$blocks["main.table.row"], etc.
    also builds the reverse parse order.
    */


    function maketree($con,$parentblock="") {
        $blocks=array();
        $con2=explode($this->block_start_delim,$con);
        if (!empty($parentblock)) {
            $block_names=explode(".",$parentblock);
            $level=sizeof($block_names);
        } else {
            $block_names=array();
            $level=0;
        }

        foreach ($con2 as $k=>$v) {
            $patt="($this->block_start_word|$this->block_end_word)\s*(\w+)\s*$this->block_end_delim(.*)";
            if (preg_match_all("/$patt/ims",$v,$res,PREG_SET_ORDER)) {
                // $res[0][1] = BEGIN or END
                // $res[0][2] = block name
                // $res[0][3] = kinda content

                if (strtoupper($res[0][1])==$this->block_start_word) {
                    $parent_name=implode(".",$block_names);
                    $block_names[++$level]=$res[0][2];                          /* add one level - array("main","table","row")*/
                    $cur_block_name=implode(".",$block_names);  /* make block name (main.table.row) */
                    $this->block_parse_order[]=$cur_block_name; /* build block parsing order (reverse) */
                    // eliminates assign error in E_ALL reporting
                    $blocks[$cur_block_name]=isset($blocks[$cur_block_name]) ? $blocks[$cur_block_name] . $res[0][3] : $res[0][3];  /* add contents */
                    $blocks[$parent_name].= str_replace('\\', '', $this->tag_start_delim) . "_BLOCK_.$cur_block_name" . str_replace('\\', '', $this->tag_end_delim); /* add {_BLOCK_.blockname} string to parent block */
                    $this->sub_blocks[$parent_name][]=$cur_block_name;      /* store sub block names for autoresetting and recursive parsing */
                    $this->sub_blocks[$cur_block_name][]="";        /* store sub block names for autoresetting */
                } else if (strtoupper($res[0][1])==$this->block_end_word) {
                    unset($block_names[$level--]);
                    $parent_name=implode(".",$block_names);
                    $blocks[$parent_name].=$res[0][3];  /* add rest of block to parent block */
                }
            } else { /* no block delimiters found */
            $tmp = implode(".",$block_names); // Saves doing multiple implodes - less overhead
            if ($k)
            $blocks[$tmp].=$this->block_start_delim;
            $blocks[$tmp]=isset($blocks[$tmp]) ? $blocks[$tmp] . $v : $v; // eliminates assign error in E_ALL reporting
            }
        }
        return $blocks;
    }

    /***[ store_filevar_parents ]***********************************************/
    /*
    store container block's name for file variables
    */

    function store_filevar_parents($blocks){
        $parents=array();
        foreach ($blocks as $bname=>$con) {
            preg_match_all($this->filevar_delim,$con,$res);
            foreach ($res[1] as $k=>$v)
            $parents[$v][]=$bname;
        }
        return $parents;
    }

    /***[ error stuff ]*********************************************************/
    /*
    sets and gets error
    */

    function get_error()    {
        // JRC: 3/1/2003 Added ouptut wrapper and detection of output type for error message output
        if ($this->OutputType == 'HTML') {
            return ($this->ERROR=="")?0: "<b>[XTemplate]</b><ul>" . nl2br(str_replace('* ', '<li>', str_replace(" *\n", "</li>\n", $this->ERROR))) . "</ul>";
        } else {
            return ($this->ERROR=="")?0: "[XTemplate] " . str_replace(" *\n", "\n", $this->ERROR);
        }
    }


    function set_error($str)    {
        //$this->ERROR="<b>[XTemplate]</b>&nbsp;<i>".$str."</i>";
        // JRC: 3/1/2003 Made to append the error messages
        $this->ERROR .= '* ' . $str . " *\n";
        // JRC: 3/1/2003 Removed trigger error, use this externally if you want it eg. trigger_error($xtpl->get_error())
        //trigger_error($this->get_error());
    }

    /***[ getfile ]*************************************************************/
    /*
    returns the contents of a file
    */

    function getfile($file) {
        if (!isset($file)) {
            // JC 19/12/02 added $file to error message
            $this->set_error("!isset file name!" . $file);
            return "";
        }

        // check if filename is mapped to other filename
        if (isset($this->FILES))
        {
            if (isset($this->FILES[$file]))
            $file = $this->FILES[$file];
        }
        // prepend template dir
        if (!empty($this->tpldir))
        $file = $this->tpldir."/".$file;

        if (isset($this->filecache[$file]))
        $file_text=$this->filecache[$file];
        else {
            if (is_file($file)) {
                if (!($fh=fopen($file,"r"))) {
                    $this->set_error("Cannot open file: $file");

                    return "";
                }

                $file_text=fread($fh,filesize($file));
                fclose($fh);
            } else {
                // NW 17Oct 2002 : Added realpath around the file name to identify where the code is searching.
                $this->set_error("[" . realpath($file) . "] ($file) does not exist");
                $file_text="<b>__XTemplate fatal error: file [$file] does not exist__</b>";
                trigger_error("File does not exist: " . $file, E_USER_ERROR);
            }
            $this->filecache[$file]=$file_text;
        }
        return $file_text;
    }

    /***[ r_getfile ]***********************************************************/
    /*
    recursively gets the content of a file with {FILE "filename.tpl"} directives
    */


    function r_getfile($file) {
        $text=$this->getfile($file);
        while (preg_match($this->file_delim,$text,$res)) {
            $text2=$this->getfile($res[1]);
            $text=preg_replace("'".preg_quote($res[0])."'",$text2,$text);
        }

        return $text;
    }


} /* end of XTemplate class. */

/*
Revision 1.2  2001/09/19 14:11:25  cranx
fixed a bug in the whitespace-stripping block variable interpolating regexp.

Revision 1.1  2001/07/11 10:42:39  cranx
added:
- filename substitution, no nested arrays for the moment, sorry
(including happens when assigning, so assign filevar in the outside blocks first!)

Revision 1.5  2001/07/11 10:39:08  cranx
added:
- we can now specify base dir
- array_loop()
- trigger_error in set_error

modified:
- newline bugs fixed (for XML)
- in out(): content-length header added
- whiles changed to foreach
- from now on, the class is php4 only :P


*/


?>