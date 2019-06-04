<?
if(!$argv){
	echo "403 Forbidden!";
	die();
}

$count = 5;
$url = "https://lenta.ru/rss";
$response = simplexml_load_file($url);

$key = 0;
$items = array();
foreach($response->channel->item as $item){
	if(count($items) < $count){
		$items[] = array('title'=>$item->title->__toString(), 'link'=>$item->link->__toString(), 'description'=>$item->description->__toString());
	}
}

foreach ($items as $item){
    print $item['title'].' '.$item['link'].' '.$item['description'].'/n';
}
