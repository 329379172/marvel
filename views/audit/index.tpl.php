<?php Fragments('audit/fragment','navigation')?>

<div>
<form action="/audit/search" method="get" >
	<input type="text" id="search" name="search" value="" /> <input type="submit" value="查询" />
</form>

</div>
<div>
	<ul>
		<?php foreach($dirtyword as $dw):?>
			<li id="dirty_li_<?=$dw['dw_id']?>"><input type="text" id="account" name="account" value="<?=$dw['words']?>" /> <a href="javascript:void(0)"  onclick="dirty_delete(<?=$dw['dw_id']?>)">删除</a></li>
		<?php endforeach;?>
	</ul>
</div>

<script>
//删除关键字
function dirty_delete(dw_id){
	$.get("/audit/dirtyword/delete?id="+dw_id, function(result){

		if(result  == 'success'){
			$("#dirty_li_"+dw_id).remove();
		}else{
			alert("操作失败");
		}
		
	});
}
</script>
 <script src="/static/js/jquery.1.11.2.min.js"></script>

