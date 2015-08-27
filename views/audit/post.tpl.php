<?php Fragments('audit/fragment','navigation')?>

<div>
<form action="/audit/search" method="get" >
	<input type="text" id="search" name="search" value="" /> <input type="submit" value="查询" />
</form>

</div>
<div>
	<ul>
		<?php foreach($log as $lg):?>
		<li>

			<p><?=$lg['title']?></p>
			<p><?=$lg['contents']?></p>
			<p> <?=timeByDay($lg['posts_time'])?> - <?=$lg['ip_section1']?>.<?=$lg['ip_section2']?>.<?=$lg['ip_section3']?>.<?=$lg['ip_section4']?>(<?=$lg['ip_city']?>) - 命中的脏词(<?=$lg['dirty_stock']?>):<?=$lg['hit_dirty_words']?></p>
		</li>
		<?php endforeach;?>
	</ul>
	<div>
		<?=$__page_html?>
	</div>
</div>