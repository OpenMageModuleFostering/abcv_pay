<?php
    $total_page = (integer)( $total_template / $itemsPerPage );
    if (( $total_template / $itemsPerPage ) > $total_page)
        $total_page += 1;
    $next_page = ($current_page < $total_page) ? $current_page+1: $total_page;
    $last_page = ($current_page > 1) ? $current_page-1: 1;
    $last_page_disabled = "";
    $next_page_disabled = "";
    $last_page_css = "cursor: pointer;";
    $next_page_css = "cursor: pointer;";
    if ($current_page == 1)
    {
        $last_page_disabled = "_off";
        $last_page_css = "";
    }
    if ($current_page == $total_page)
    {
        $next_page_disabled = "_off";
        $next_page_css = "";
    }
        

?>
<table cellspacing="0" class="actions">
        <tbody><tr>
            <td class="pager">
                <?php if ($total_template != 0): ?>
                Page
                <img style="<?php echo $last_page_css ?>" onclick="javascript:obj_saved_template.get('<?php echo $last_page;?>')" class="arrow" alt="Go to Previous page" src="/skin/adminhtml/default/default/images/pager_arrow_left<?php echo $last_page_disabled?>.gif">

                <input type="text" class="input-text page" value="<?php echo $current_page?>" name="page">
                
                <img style="<?php echo $next_page_css ?>" onclick="javascript:obj_saved_template.get('<?php echo $next_page;?>')"  class="arrow" alt="Go to Previous page" src="/skin/adminhtml/default/default/images/pager_arrow_right<?php echo $next_page_disabled?>.gif">

                of <?php echo $total_page ?> pages <span class="separator">|</span> View
                
                <select onchange="javascript:obj_saved_template.changeItemPerPage(this)" name="limit">
                    <option <?php echo ($itemsPerPage==20)?"selected":""?> value="20">20</option>
                    <option <?php echo ($itemsPerPage==30)?"selected":""?> value="30">30</option>
                    <option <?php echo ($itemsPerPage==50)?"selected":""?> value="50">50</option>
                    <option <?php echo ($itemsPerPage==100)?"selected":""?> value="100">100</option>
                    <option <?php echo ($itemsPerPage==200)?"selected":""?> value="200">200</option>
                </select>
                
                per page<span class="separator">|</span>
                <?php endif; ?>
                Total <?php echo $total_template?> templates found
            </td>
            <td class="filter-actions a-right">
                <button onclick="javascript:obj_saved_template.resetFilter(this)" class="scalable " type="button" title="Reset Filter"><span><span><span>Reset Filter</span></span></span></button>
                <button onclick="javascript:obj_saved_template.search(this)" class="scalable task" type="button" title="Search"><span><span><span>Search</span></span></span></button>        
            </td>
        </tr>
    </tbody></table>