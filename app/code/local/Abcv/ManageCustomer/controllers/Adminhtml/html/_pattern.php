<?php include('_paging.php'); ?>
    <div class="grid">
        <div class="hor-scroll">
            <table cellspacing="0" id="customer_orders_grid_table" class="data">
                <colgroup>
                    <col width="100">
                    <col>
                    <col>
                    <col>
                    <col width="500">
                </colgroup><thead>
                    <tr class="headings">
                        <th>
                            <span class="nobr">
                                <a 
                                <?php
                                    if (!empty($postData['desc']) && $postData['desc'] == 'save_id')
                                    {
                                        echo "class=sort-arrow-desc title=desc";
                                    }
                                    else if (!empty($postData['asc']) && $postData['asc'] == 'save_id')
                                    {
                                        echo "class=sort-arrow-asc  title=asc";
                                    }
                                    else
                                    {
                                        echo "class=not-sort";   
                                    }
                                ?>
                                 title="asc" href="javascript:void(0);" onclick="javascript:obj_saved_template.sort(this,'save_id');">
                                <span class="sort-title">Saved #</span></a>
                            </span>
                        </th>
                        
                        <th>
                            <span class="nobr">
                                <a <?php
                                    if (!empty($postData['desc']) && $postData['desc'] == 'date_at')
                                    {
                                        echo "class=sort-arrow-desc title=desc";
                                    }
                                    else if (!empty($postData['asc']) && $postData['asc'] == 'date_at')
                                    {
                                        echo "class=sort-arrow-asc  title=asc";
                                    }
                                    else
                                    {
                                        echo "not-sort";   
                                    }
                                ?> href="javascript:void(0);" onclick="javascript:obj_saved_template.sort(this,'date_at');"><span class="sort-title">Created Date</span></a>
                            </span>
                        </th>

                        <th>
                            <span class="nobr">
                                <a <?php
                                    if (!empty($postData['desc']) && $postData['desc'] == 'product_id')
                                    {
                                        echo "class=sort-arrow-desc title=desc";
                                    }
                                    else if (!empty($postData['asc']) && $postData['asc'] == 'product_id')
                                    {
                                        echo "class=sort-arrow-asc  title=asc";
                                    }
                                    else
                                    {
                                        echo "not-sort";   
                                    }
                                ?> href="javascript:void(0);" onclick="javascript:obj_saved_template.sort(this,'product_id');"><span class="sort-title">Product Id</span></a></span>
                        </th>

                        <th><span class="nobr"> Action</span></th>
                        <th class="last"><span class="nobr">Image Template</span></th>
                    </tr>
                    <tr class="filter">
                        <th>
                            <div class="field-100">
                                <input type="text" onchange="javascript:obj_saved_template.changefeild(this)" class="input-text no-changes" value="<?php echo $postData['save_id'] ?>" id="save_id">
                            </div>
                        </th>
                        
                        <th>
                            <div class="range">
                                <div class="range-line date">
                                    <span class="label">From:</span>
                                    <input type="text" class="input-text no-changes"  value="<?php echo $postData['from_date'] ?>" onchange="javascript:obj_saved_template.changefeild(this)" id="from_date" readonly>
                                    <img title="Date selector" id="_btn_from_date" class="v-middle" alt="" src="/skin/adminhtml/default/default/images/grid-cal.gif">
                                </div>
                            
                                <div class="range-line date">
                                    <span class="label">To :</span>
                                    <input type="text" class="input-text no-changes" value="<?php echo $postData['to_date'] ?>"  onchange="javascript:obj_saved_template.changefeild(this)" id="to_date" readonly>
                                    <img title="Date selector" class="v-middle" id="_btn_to_date" alt="" src="/skin/adminhtml/default/default/images/grid-cal.gif">
                                </div>
                            </div>
                        <th>
                            <div class="field-100">
                                <input type="text"  onchange="javascript:obj_saved_template.changefeild(this)" class="input-text no-changes"  value="<?php echo $postData['product_id'] ?>" id="product_id">
                            </div>
                        </th>
                        <th class="  ">&nbsp;</th>
                        <th class="no-link last"></th>
                    </tr>
                </thead>

                <tbody id="tbody_update">
                    <?php include('_table.php'); ?>

                    
                </tbody>
            </table>
        </div>
    </div>