<?php
/**
 * Created by PhpStorm.
 * User: liuzeming
 * Date: 2018/6/29
 * Time: 20:43
 */

namespace CMS\MTeamServicesSDK\Until;


class GenerateTreeUntil extends BaseUntil
{
    /**
     * Generate a tree
     *
     * @param  array  $itmes = [
     *     [
     *         'id' => 1,
     *         'parent_id' => 0, //parentKey
     *         'name' => 'name1'
     *     ],
     * ]
     * @param  string $parentKey
     *
     * @return array
     */
    public static function generateTree(array $items, $parentKey = 'parent_id')
    {
        if (empty($items)) {
            return [];
        } else {
            $items = array_column($items, null, 'id'); //NULL，此时将返回整个数组（配合index_key参数来重置数组键的时候，非常管用）
            $tree = array();
            foreach ($items as $item) {
                if (isset($items[$item[$parentKey]])) {
                    $items[$item[$parentKey]]['children'][] = &$items[$item['id']];
                } else {
                    $tree[] = &$items[$item['id']];
                }
            }
            unset($items);
            return $tree;
        }
    }


    //方案一:递归实现无限级分类,适合于下拉列表框，输出时不保留层级关系，仅仅根据lev即分类等级来区分(前端展示时，不同长度)

    public static function generateTreeV1($items, $parentId=0, $parentKey = 'parent_id', $lev=1)
    {
        //$lev=1，代表第几次查询，即第几级商品分类
        $tree = array();
        //保存所有商品分类到subs数组，仅仅用lev等级来区分是第几级商品分类
        if (empty($items)) {
            return array();
        }
        foreach ($items as $item) {
           //第一次找到一级商品分类中的某一个，然后找到它对应的二级分类，以此类推....
            if($item[$parentKey] == $parentId) {
                $item['lev'] = $lev;
                $tree[] = $item;
                //数组合并
                $tree = array_merge($tree, self::generateTreeV1($items, $item['id'], $parentKey, $lev+1));
            }

        }
        return $tree;
    }

    //方案二:采用递归实现无限级分类,适合于做菜单栏，在输出时要保留层级关系

    public static function generateTreeV2($items, $parentId=0, $parentKey='parent_id', $lev=1)
    {

        //$lev=1，代表第几次查询，即第几级分类

        $tree = array();//用来保存某一个分类的所有子元素(下一级分类)

        if (empty($items)) {
            return array();
        }

        foreach($items as $item) {

            if($item[$parentKey] == $parentId) { //父节点id判断

                $item['lev'] = $lev;

                $item["child"] =  self::generateTreeV2($items, $item['id'], $parentKey, $lev+1);

                //获取所有子节点(下一级分类)，保存为一个数组的一个元素

                //$item["list"]不能在加[]的原因:方法里面本身就是foreach的,返回的就是所有子节点

                if (empty($item["child"])) {//如果没有下一级分类，就将这个元素删除
                    unset($item["child"]);
                }

                $tree[] = $item; //将子元素(下一级分类)保存为数组的一个元素
            }

        }

        return $tree;
    }

    /**
     * 获取某个子节点的所有父节点,适用于新闻，论坛等
     * @param $items
     * @param $childrenId
     * @param int $lev
     * @return array
     */
    public static function getParentsByCidV1($items, $childrenId, $lev=0)
    {
        //$lev代表第几级父分类，0代表自身
        $pathList = array();
        while ($childrenId != 0) {
            foreach ($items as $item) {
                if ($item['id'] == $childrenId) {
                    $item['lev'] = $lev;
                    $pathList[] = $item;
                    $childrenId = $item['parent_id'];
                    $lev++;
                }
            }
        }
        return $pathList;
    }

    public static function getParentsByCidV2($items, $childrenId, $lev=0)
    {
        //$lev代表第几级父分类，0代表自身
        $pathList = array(); //用来保存所有父分类的数组
        foreach ($items as $item) {
            if($item['id'] == $childrenId){
                $item['lev'] = $lev;
                $pathList[] = $item; //当前分类
                $pathList = array_merge($pathList, self::getParentsByCidV2($items, $item['parent_id'], $lev+1)); //获取父分类
            }
        }
        return $pathList;
    }
}

$area = array(
    array('id'=>1,'name'=>'安徽','parent_id'=>0),
    array('id'=>2,'name'=>'海淀','parent_id'=>0),
    array('id'=>3,'name'=>'濉溪县','parent_id'=>1),
    array('id'=>4,'name'=>'昌平','parent_id'=>3),
    array('id'=>5,'name'=>'淮北','parent_id'=>3),
    array('id'=>6,'name'=>'朝阳','parent_id'=>5),
    array('id'=>7,'name'=>'北京','parent_id'=>0),
    array('id'=>8,'name'=>'上地','parent_id'=>2)
);
$result = GenerateTreeUntil::generateTreeV1($area, 'parent_id', 0, 1);
print_r($result);


$area = array(
    array('id'=>1,'name'=>'安徽','parent_id'=>0),
    array('id'=>2,'name'=>'海淀','parent_id'=>0),
    array('id'=>3,'name'=>'濉溪县','parent_id'=>1),
    array('id'=>4,'name'=>'昌平','parent_id'=>3),
    array('id'=>5,'name'=>'淮北','parent_id'=>3),
    array('id'=>6,'name'=>'朝阳','parent_id'=>5),
    array('id'=>7,'name'=>'北京','parent_id'=>0),
    array('id'=>8,'name'=>'上地','parent_id'=>2)
);
$result = GenerateTreeUntil::generateTreeV2($area, 0, 'parent_id', 1);
print_r($result);


$area = array(
    array('id'=>1,'name'=>'安徽','parent_id'=>0),
    array('id'=>2,'name'=>'海淀','parent_id'=>0),
    array('id'=>3,'name'=>'濉溪县','parent_id'=>1),
    array('id'=>4,'name'=>'昌平','parent_id'=>3),
    array('id'=>5,'name'=>'淮北','parent_id'=>3),
    array('id'=>6,'name'=>'朝阳','parent_id'=>5),
    array('id'=>7,'name'=>'北京','parent_id'=>0),
    array('id'=>8,'name'=>'上地','parent_id'=>2)
);
var_dump(GenerateTreeUntil::getParentsByCidV1($area,3));


$area = array(
    array('id'=>1,'name'=>'安徽','parent_id'=>0),
    array('id'=>2,'name'=>'海淀','parent_id'=>0),
    array('id'=>3,'name'=>'濉溪县','parent_id'=>1),
    array('id'=>4,'name'=>'昌平','parent_id'=>3),
    array('id'=>5,'name'=>'淮北','parent_id'=>3),
    array('id'=>6,'name'=>'朝阳','parent_id'=>5),
    array('id'=>7,'name'=>'北京','parent_id'=>0),
    array('id'=>8,'name'=>'上地','parent_id'=>2)
);
var_dump(GenerateTreeUntil::getParentsByCidV2($area,3));