<?php


namespace App\Http\Controllers\Admin;

use App\Category;
use App\DB;
use App\Auction;
use App\AuctionCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('check.admin');
    }

    public function index(Request $request)
    {
        $data = [
            "categoryMenuHTML" => Category::getCategoriesAdmin(),
            "categories" => Category::resultArrayToClassArray(DB::select("SELECT c.id, c.name, c.manual_order, c2.name as name_parent, c2.id as parent_id FROM categories c INNER JOIN categories c2 ON c.parent_id = c2.id ORDER by c2.name"))
        ];
        return view("admin.categories.index")->with($data);
    }

    public function show($id)
    {
        return response()->json(['data' => Category::oneWhere('id', $id) ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $category = new \App\Category();
        $category->name = $data["new_category"];
        if($data["new_order"] >= 1) {
            $category->manual_order = $data['new_order'];
        }
        $category->parent_id = $data["change_parent"];
        $category->save();

        $this->checkEmptyCategory($data["change_parent"]);

        $data = [
            "categoryMenuHTML" => Category::getCategoriesAdmin()
        ];

        return response()->json(['success' => "added", 'data' => $data ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();

        //verplaats categorie naar nieuwe parent
        $category = Category::oneWhere('id', $id);
        $category->parent_id = $data["new_parent"];
        $category->manual_order = $data['new_order'];
        $category->name = $data["new_name"];
        $category->update(true);

        // new_parent veilingen heeft
        $this->checkEmptyCategory($data["new_parent"]);

        $data = [
            "categoryMenuHTML" => Category::getCategoriesAdmin()
        ];

        return response()->json(['success' => "updated", 'data' => $data]);
    }

    public function destroy(Request $request, $id)
    {
        $auctionsCheck = AuctionCategory::resultArrayToClassArray(DB::select("SELECT * FROM auction_categories WHERE category_id=:new_parent", [
            "new_parent" => $id
        ]));

        $parentCheck = Category::resultArrayToClassArray(DB::select("SELECT * FROM categories WHERE parent_id=:new_parent", [
            "new_parent" => $id
        ]));

        if (empty($auctionsCheck))
        {
            if(empty($parentCheck)){
                $category = Category::deleteWhere('id', $id);

                $data = [
                    "categoryMenuHTML" => Category::getCategoriesAdmin()
                ];

                return response()->json(['success' => "deleted", 'data' => $data]);
            }
        }

        $data = [
            "categoryMenuHTML" => Category::getCategoriesAdmin()
        ];

        return response()->json(['error' => 'delete', 'data' => $data]);
    }

    private function checkEmptyCategory($new_parent)
    {
        $auctionsCheck = AuctionCategory::resultArrayToClassArray(DB::select("SELECT * FROM auction_categories WHERE category_id=:new_parent", [
            "new_parent" => $new_parent
        ]));

        if (!empty($auctionsCheck)) {
            $category = new \App\Category();
            $category->name = "overige";
            $category->parent_id = $new_parent;
            $category->save();

            foreach($auctionsCheck as $auction) {
                $auction->category_id = $category->id;
                $auction->update(true);
            }
        }
    }
}
