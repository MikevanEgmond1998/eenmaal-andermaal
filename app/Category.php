<?php

namespace App;


use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends SuperModel
{
    public static function printTree($categories, &$allCategories, $level = 0)
    {
        $i = 1;
        $printStr = "";
        foreach ($categories as $category) {
            $childCategories = [];
            foreach ($allCategories as $childCat) {
                if ($childCat->parent_id === $category->id)
                    array_push($childCategories, $childCat);
            }

            $classes = 'category category-' . $level;

            if ($level == 0) {
                $classes .= ' col-sm-6 col-md-3';
            }
            if ($level != 0) {
                $classes .= ' d-none';
            }

            if ($level < 5 && count($childCategories) > 0) {
                $printStr .= '<div class="' . $classes . ' category-hoverable"><span class="clickable-submenu user-select-none">' . $category->name . ($level != 0 ? " <i class='fas fa-arrow-down category-arrow'></i>" : "") . " </span>";
                $printStr .= self::printTree($childCategories, $allCategories, $level + 1);
                $printStr .= '</div>';
            } else {
                $printStr .= '<a href="/categorie/' . $category->id . '" class="' . $classes . ' user-select-none">' . $category->name . " <i class='fas fa-arrow-right category-arrow'></i>";
                $printStr .= '</a>';
            }
        }
        return $printStr;
    }

    public static function getCategories()
    {
        $allCategories = self::allOrderBy('-manual_order DESC, name');

        $checkSum = md5(serialize($allCategories));

        $fileName = "cache/categories.html";
        if (isset($_COOKIE["category_hash"]) && $_COOKIE["category_hash"] == $checkSum) {
            return Storage::disk('local')->get($fileName);
        }

        $mainCategories = [];
        foreach ($allCategories as $category) {
            if ($category->parent_id == -1)
                array_push($mainCategories, $category);
        }

        $html = self::printTree($mainCategories, $allCategories);
        Storage::disk('local')->put($fileName, $html);
        setcookie("category_hash", $checkSum);
        return $html;
    }

    public static function printTreeAdmin($categories, &$allCategories, $level = 0)
    {
        $i = 1;
        $printStr = "";
        foreach ($categories as $category) {

            $childCategories = [];
            foreach ($allCategories as $childCat) {
                if ($childCat->parent_id === $category->id)
                    array_push($childCategories, $childCat);
            }

            $classes = 'category category-' . $level;

            if ($level != 0) {
                $classes .= ' d-none';
            }
            $marginLeft = 10 * $level;

            // $category->manual_order

            // TODO code kan beter, is nu een beetje dubbel.

            if (count($childCategories) > 0) {

                $printStr .= '<div class="' . $classes . ' category-hoverable" style="margin-left:'.$marginLeft.'px">';
                $printStr .= '<span onclick="categorySelected();" id="' . $category->id . '" class="clickable-submenu user-select-none">' . ($category->manual_order ? $category->manual_order . ' ' : '') . $category->name . " <i class='fas fa-arrow-down category-arrow'></i>" . " </span>";
                $printStr .= self::printTreeAdmin($childCategories, $allCategories, $level + 1);
                $printStr .= '</div>';

            } else {

                $printStr .= '<a onclick="categorySelected();" id="' . $category->id . '" href="javascript:void(0);" class="' . $classes . ' user-select-none" style="margin-left:'.$marginLeft.'px">' . ($category->manual_order ? $category->manual_order . ' ': '') . $category->name . " <i class='fas fa-arrow-right category-arrow'></i>";
                $printStr .= '</a>';
                
            }

            // if (count($childCategories) > 0) {
            //     $printStr .= '<div class="' . $classes . ' category-hoverable" style="margin-left:'.$marginLeft.'px"><span onclick="categorySelected();" id="' . $category->id . '" class="clickable-submenu user-select-none">' . $category->name . " <i class='fas fa-arrow-down category-arrow'></i>" . " </span>";
            //     $printStr .= self::printTreeAdmin($childCategories, $allCategories, $level + 1);
            //     $printStr .= '</div>';
            // } else {
            //     $printStr .= '<a onclick="categorySelected();" id="' . $category->id . '" href="javascript:void(0);" class="' . $classes . ' user-select-none" style="margin-left:'.$marginLeft.'px">' . $category->name . " <i class='fas fa-arrow-right category-arrow'></i>";
            //     $printStr .= '</a>';
            // }
        }
        return $printStr;
    }

    public static function getCategoriesAdmin()
    {
        $allCategories = self::allOrderBy('-manual_order DESC, name');
        $mainCategories = [];
        foreach ($allCategories as $category) {
            if ($category->parent_id == -1)
                array_push($mainCategories, $category);
        }
        $html = self::printTreeAdmin($mainCategories, $allCategories);
        return $html;
    }

    /**
     * Get the parent category as array
     * @return array
     */
    public function getParentCategory()
    {
        $category = DB::selectOne("
            SELECT TOP 1 * FROM categories WHERE id=:parent_id
        ", [
            "parent_id" => $this->parent_id
        ]);
        return $category ?: ["name" => ""];
    }

    /**
     * Get the children categories as array
     * @return array
     */
    public function getChildrenCategories()
    {
        $categories = DB::select("
            SELECT * FROM categories WHERE parent_id=:id
        ", [
            "id" => $this->id
        ]);
        return $categories;
    }

    /**
     * Get the children categories as string
     * @return string
     */
    public function getChildrenCategoriesString()
    {
        $returnStr = "";
        foreach ($this->getChildrenCategories() as $category) {
            $returnStr .= $category["name"] . ", ";
        }
        return $returnStr;
    }

}
