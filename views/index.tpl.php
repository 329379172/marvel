<?=Fragments('fragment','bbs_header')?>

首页内容 </br>

<?php foreach($forum as $f):?>
	<a href="/thread/<?=$f['forum_id']?>"><?=$f['forum_name']?></a>
<?php endforeach;?>

<?=Fragments('fragment','bbs_footer')?>
