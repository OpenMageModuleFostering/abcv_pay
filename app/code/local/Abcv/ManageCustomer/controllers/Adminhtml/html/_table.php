<?php
    $is_exsits_template = false;
    foreach ($save_template as $key=>$template):
        $is_exsits_template = true;
        $template = (object) $template;
        $product_id = str_replace('product_','',$template->product_id)
?>
<tr title="" class="even pointer">
    <td class=" "><?php echo $template->save_id?></td>
    
    <td class=" ">
        <?php 
            $d = new DateTime($template->date_at);
            echo $d->format('M d, Y h:i:s A');     
        ?>
    </td>
    <td class=" "><?=$product_id?></td>
    <td class=" ">
        <?php
            $url = Mage::getBaseUrl()."editor/index/template/template/".$template->template_id."/product/".$product_id."/productstyle/".$template->productstyle_id."/save/".$template->save_id."/is_admin/".strtotime("now");
        ?>
        <a target="_blank" href="<?php echo $url ?>">View &amp; Change</a>                    
    </td>
    <td class="last">
        <?php
            $image = $template->image;
            $image = json_decode($image);
            foreach($image as $ikey => $img):
        ?>
            <div style="float:left;padding-left: 5px;" class="photo">
                <a href="#<?=$template->save_id?>_<?=$ikey?>">
                    <img src="<?='/image.php?ID='. $template->productstyle_id."_".$template->save_id. '&savetemplates'//$img->imageData?>" width="100" height="100" />
                </div>
            <div id="<?=$template->save_id?>_<?=$ikey?>" style="display: none;">
                <img src="<?='/image.php?ID='. $template->productstyle_id."_".$template->save_id. '&savetemplates'//$img->imageData?>">
            </div>
        <?php endforeach;    
        ?>
    </td>
</tr>
<?php endforeach; ?>
<?php if (!$is_exsits_template) : ?>
<tr>
    <td colspan="5" class="a-center"> No template </td>
</tr>
<?php endif; ?>
