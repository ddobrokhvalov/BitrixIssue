<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

class testIblockElementList{
	
	public $cache_time;
    public $cache_path;
    public $cache;
    
    //При создании объекта задаем параметры кэширования
    function __construct($cache_time=3600, $cache_path="test_ib_cache"){
        $this->cache= new CPHPCache();
        $this->cache_time = $cache_time;
        $this->cache_path = $cache_path;
	}
	
	//Метод получения списка без кэша. Параметры метода полностью аналогичные CIBlockElement::GetList
	private function getElementList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $arGroupBy=false, $arNavStartParams=false, $arSelectFields=array('ID','NAME')){
		CModule::IncludeModule('iblock');
        $res = CIBlockElement::GetList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
        $arResult = array();
        while($rec = $res->fetch()){
			$arResult[] = $rec;
		}
		return $arResult;
	}
	
	//Метод с кэшем. Параметры метода полностью аналогичные CIBlockElement::GetList и предыдущему методу.
	public function getCachedElementList($arOrder=array("SORT"=>"ASC"), $arFilter=array(), $arGroupBy=false, $arNavStartParams=false, $arSelectFields=array('ID','NAME')){
		$arParams = array('arOrder'=>$arOrder, 'arFilter'=>$arFilter, 'arGroupBy'=>$arGroupBy, 'arNavStartParams'=>$arNavStartParams, 'arSelectFields'=>$arSelectFields);
		
		$cache_id=md5(serialize($arParams));
		$arResult = false;
		//дебаг параметров кэша
		/*print_r("<pre>");
		print_r(array($this->cache_time, $cache_id, $this->cache_path));
		print_r("</pre>");*/
		/**
		 * Если есть кэш и он не просрочен
		 * берем данные из кэша
		 */
		if ($this->cache_time > 0 && $this->cache->InitCache($this->cache_time, $cache_id, $this->cache_path)){
			$cache_res = $this->cache->GetVars();
            if (is_array($cache_res["arResult"]) && (count($cache_res["arResult"]) > 0)){
                $arResult = $cache_res["arResult"];
                //print_r("из кэша");
            }
		}else{
			/**
			 * иначе чистим кэш
			 */
			$this->clearCache();
		}
		
		/**
		 * Если кэш пустой или просрочен
		 * или время жизни кэша = 0
		 * получаем данные из ИБ некэшированным методом.
		 */
		if(!$arResult){
			$arResult = $this->getElementList($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
			//print_r("без кэша");
			if ($this->cache_time > 0)
            {
                $this->cache->StartDataCache($this->cache_time, $cache_id, $this->cache_path);
                $this->cache->EndDataCache(array("arResult"=>$arResult));
            }
		}
		return $arResult;
	}
	
	private function clearCache(){
		$this->cache->CleanDir( $this->cache_path );
	}
}

$test_obj = new testIblockElementList(300);
$test_list = $test_obj->getCachedElementList(array("SORT"=>"ASC"), array("IBLOCK_ID"=>"1"));
print_r("<pre>");
print_r($test_list);
print_r("</pre>");
