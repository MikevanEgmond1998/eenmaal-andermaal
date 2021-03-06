<?php

namespace App\Http\Controllers;

use App\Auction;
use App\AuctionCategory;
use App\AuctionHit;
use App\AuctionPaymentMethod;
use App\AuctionShippingMethod;
use App\Category;
use App\AuctionImage;
use App\Country;
use App\Mail\AuctionEnded;
use App\Mail\AuctionEnding;
use App\Mail\SellerVerification;
use App\PaymentMethod;
use App\ShippingMethod;
use App\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use function App\Helpers\countLengthNewlinesOneCharacter;
use function App\Helpers\pizzaaaa;
use function App\Helpers\textAreaNewlinesToSimpleNewline;

class AuctionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('check.user.is.blocked');
        $this->middleware('check.user')->except(['show', 'mailFinishedAuctionOwners']);
    }

    public function index()
    {
        return abort(404);
    }
    /**
     * Show the requested auction
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|void
     */

    public function create()
    {
        $mainCategories = Category::resultArrayToClassArray(DB::select(
            "SELECT * FROM categories WHERE parent_id=-1 ORDER BY name ASC"
        ));

        $data = [
            "mainCategories" => Category::allWhereOrderBy("parent_id", -1, 'name'),
            "shippingMethods" => ShippingMethod::all(),
            "paymentMethods" => PaymentMethod::all(),
            "countries" => Country::allOrderBy('country')
        ];
        return view("auctions.create")->with($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, array(
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'start_price' => ['require', 'regex:/^\d+(\.\d{1,2})?$/'],
            'payment_instruction' => ['nullable', 'string'],
            'duration' => ['required', 'numeric'],
            'image.*' => ['required', 'mimes:jpeg,jpg,png', 'max:10000'], //10000kb/10mb
            'city' => ['required', 'string', 'max:100'],
        ));
        if (countLengthNewlinesOneCharacter($request->get("description")) > 500)
            return redirect()->back()->withInput($request->all())->withErrors(["description" => "Omschrijving mag niet uit meer dan 500 tekens bestaan."]);
        if (countLengthNewlinesOneCharacter($request->get("payment_instruction")) > 255)
            return redirect()->back()->withInput($request->all())->withErrors(["payment_instruction" => "Extra betalingsinstructies mag niet uit meer dan 255 tekens bestaan."]);

        $catId = -1;
        foreach ($request->get("category") as $key => $value) {
            if ($value != -2) {
                $catId = $value;
            }
        }
        if (count(Category::allWhereOrderBy("parent_id", $catId, 'name')))
            return redirect()->back()->withInput($request->all())->withErrors(["category" => "Je mag geen rubriek kiezen die subrubrieken heeft"]);
        if (
            $request->get("duration") != "1" &&
            $request->get("duration") != "3" &&
            $request->get("duration") != "5" &&
            $request->get("duration") != "7" &&
            $request->get("duration") != "10"
        )
            return redirect()->back()->withInput($request->all())->withErrors(["duration" => "Je mag alleen 1, 3, 5, 7 of 10 invullen"]);
        if (
            DB::selectOne("SELECT * FROM countries WHERE country_code=:country_code", [
                "country_code" => $request->countryCode
            ]) === false
        )
            return redirect()->back()->withInput($request->all())->withErrors(["countryCode" => "Er bestaat geen land in onze database met de ingevulde landcode"]);

        $latAndLon = $this->getLatAndLon($request->city, $request->countryCode);
        if (array_key_exists('error', $latAndLon)) {
            return redirect()->back()->withInput($request->all())->withErrors(["postal_code" => $latAndLon['error']]);
        }

        $auction = new Auction();
        $auction->user_id = $request->session()->get("user")->id;
        $auction->title = $request->title;
        $auction->description = textAreaNewlinesToSimpleNewline($request->description);
        $auction->payment_instruction = textAreaNewlinesToSimpleNewline($request->paymentInstruction);
        $auction->start_price = $request->startPrice;
        $auction->duration = $request->duration;
        $auction->end_datetime = Carbon::now()->addDays($auction->duration);
        $auction->city = $request->city;
        $auction->country_code = $request->countryCode;
        $auction->latitude = $latAndLon['lat'];
        $auction->longitude = $latAndLon['lon'];
        $auction->save();

        if ($request->file('image') != null) {
            foreach ($request->file('image') as $img) {
                $fileName = $auction->id . "/" . Str::random(10) . ".png";
                if (env("APP_ENV") == "local") {
                    Storage::disk('auction_images')->put($fileName, file_get_contents($img));
                } else {
                    Storage::disk('auction_images_server')->put($fileName, file_get_contents($img));
                }

                $auctionImage = new AuctionImage();
                $auctionImage->auction_id = $auction->id;
                $auctionImage->file_name = '/images/auctions/' . $fileName;
                $auctionImage->save();
            }
        } else {
            return redirect()->back()->withInput($request->all())->withErrors(["image.0" => ["Je moet minimaal 1 afbeelding selecteren"]]);
        }

        $auctionCategory = new AuctionCategory();
        $auctionCategory->auction_id = $auction->id;
        $auctionCategory->category_id = $catId;
        $auctionCategory->save();

        if($request->shipping !== null) {
            foreach ($request->shipping as $method) {
                $auctionShippingMethod = new AuctionShippingMethod();
                $auctionShippingMethod->auction_id = $auction->id;
                $auctionShippingMethod->shipping_id = $method;
                $auctionShippingMethod->save();
            }
        }

        if($request->payment !== null) {
            foreach ($request->payment as $method) {
                $auctionPaymentMethod = new AuctionPaymentMethod();
                $auctionPaymentMethod->auction_id = $auction->id;
                $auctionPaymentMethod->payment_id = $method;
                $auctionPaymentMethod->save();
            }
        }
        return redirect()->route("auctions.show", $auction->id);
    }

    /**
     * Request new category select HTML
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function categorySelect($id, $level)
    {

        $cats = Category::allWhereOrderBy("parent_id", $id, 'name');
        if (count($cats) === 0)
            abort(404);

        $data = [
            "level" => $level,
            "categories" => $cats,
            "selected" => false
        ];
        return view("includes.categoryselection")->with($data);
    }

    public function show($id, Request $request)
    {
        if(!is_numeric($id)){
            return abort(404);
        }

        $auction = Auction::oneWhere("id", $id);
        if ($auction === false)
            return abort(404);

        if ($auction->is_blocked == 1) {
            return view('auctions.blocked');
        }

        $user = session('user');
        //als ingelogd pak alleen userid geen ip
        AuctionHit::hit($auction, $user, $request);

        $auctionImages = $auction->getImages();
        $auctionBids = $auction->getBids();
        $auctionReviewCount = count($auction->getReviews());
        $reviewsData = [
            "count" => $auctionReviewCount,
            "average" => $auction->getReviewAverage(),
            "fiveStars" => number_format(($auctionReviewCount === 0 ? 0 : count($auction->getReviewsByRating(5)) / $auctionReviewCount) * 100) . "%",
            "fourStars" => number_format(($auctionReviewCount === 0 ? 0 : count($auction->getReviewsByRating(4)) / $auctionReviewCount) * 100) . "%",
            "threeStars" => number_format(($auctionReviewCount === 0 ? 0 : count($auction->getReviewsByRating(3)) / $auctionReviewCount) * 100) . "%",
            "twoStars" => number_format(($auctionReviewCount === 0 ? 0 : count($auction->getReviewsByRating(2)) / $auctionReviewCount) * 100) . "%",
            "oneStars" => number_format(($auctionReviewCount === 0 ? 0 : count($auction->getReviewsByRating(1)) / $auctionReviewCount) * 100) . "%"
        ];
        $data = [
            "auction" => $auction,
            "auctionImages" => $auctionImages,
            "auctionBids" => $auctionBids,
            "reviewsData" => $reviewsData,
            "auctionHits" => AuctionHit::getHits($auction)
        ];
        return view("auctions.view")->with($data);
    }

    function dateSortDesc($a, $b)
    {
        return strtotime($b) - strtotime($a);
    }

    /**
     * Get the user's auctions
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function myAuctions(Request $request)
    {
        $myAuctions = Auction::resultArrayToClassArray(DB::select("SELECT * FROM auctions WHERE user_id=:user_id ORDER BY end_datetime ASC", [
            "user_id" => $request->session()->get("user")->id
        ]));
        $auctionsCount = count($myAuctions);
        $openAuctions = [];
        $closedAuctions = [];
        for ($i = 0; $i < $auctionsCount; $i++) {
            $parsedTime = Carbon::parse($myAuctions[$i]->end_datetime);
            if (Carbon::now() >= $parsedTime) {
                array_push($closedAuctions, $myAuctions[$i]);
            } else {
                array_push($openAuctions, $myAuctions[$i]);
            }
        }
        usort($closedAuctions, function ($a, $b) {
            return strtotime($b->end_datetime) - strtotime($a->end_datetime);
        });
        $allAuctions = [
            "openAuctions" => [
                "name" => "Mijn veilingen",
                "auctions" => $openAuctions
            ],
            "closedAuctions" => [
                "name" => "Gesloten veilingen",
                "auctions" => $closedAuctions
            ]
        ];
        $data = [
            "auctionsCount" => $auctionsCount,
            "allAuctions" => $allAuctions
        ];
        return view("auctions.myauctions")->with($data);
    }

    /**
     * Get the user's won auctions
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wonAuctions(Request $request)
    {
        $userId = Session::get("user")->id;
        $auctions = Auction::resultArrayToClassArray(DB::select("
                SELECT *
                FROM auctions
                WHERE GETDATE()>auctions.end_datetime AND EXISTS(
                    SELECT * FROM bids a WHERE auction_id=auctions.id AND user_id=$userId AND a.amount IN(
                        SELECT MAX(amount) FROM bids b GROUP BY auction_id
                    )
                )
            "));

        $data = [
            'auctions' => $auctions
        ];
        return view("auctions.wonauctions")->with($data);
    }

    /**
     * Get the auctions that the user has bid on
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function bidAuctions(Request $request)
    {
        $userId = Session::get("user")->id;
        $auctions = Auction::resultArrayToClassArray(DB::select("
                WITH bidsLatest AS(
                    SELECT * FROM
                    (SELECT auction_id,user_id,created_at,
                                ROW_NUMBER() OVER (PARTITION BY auction_id ORDER BY created_at DESC) AS RowNumber
                         FROM   bids
                         WHERE  user_id = $userId) AS a
                    WHERE a.RowNumber = 1
                )

                SELECT a.*, b.created_at AS bid_created_at
                FROM bidsLatest b
                LEFT JOIN auctions a
                ON b.auction_id=a.id
                ORDER BY bid_created_at DESC
            "));

        $data = [
            'auctions' => $auctions
        ];
        return view("auctions.bidauctions")->with($data);
    }

    /**
     * Send emails to owners of auctions finished within the last minute
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mailFinishedAuctionOwners()
    {
        $finishedAuctions = Auction::resultArrayToClassArray(DB::select("
                SELECT id,title,user_id
                FROM auctions
                WHERE auctions.end_datetime > DATEADD(MINUTE, -1, GETDATE()) AND auctions.end_datetime < GETDATE()
            "));

        $endingAuctions = Auction::resultArrayToClassArray(DB::select("
                WITH finalInfo AS(
                    SELECT *
                    FROM bids
                    WHERE EXISTS(
                    SELECT bids.auction_id, bids.amount as amount
                        FROM bids bd
                        LEFT JOIN auctions
                        ON bids.auction_id=auctions.id
                        WHERE EXISTS (
                            SELECT auctions.id
                            FROM auctions
                            WHERE auctions.end_datetime > DATEADD(MINUTE, -1, DATEADD(MINUTE,10,GETDATE())) AND auctions.end_datetime < DATEADD(MINUTE,10,GETDATE()) AND bids.auction_id=auctions.id
                        )
                        AND bids.auction_id=bd.auction_id AND bids.amount=bd.amount
                    )
                )

                SELECT DISTINCT auction_id,title,email
                    FROM (
                        SELECT auction_id,user_id,amount, Rank()
                          over (Partition BY auction_id
                                ORDER BY amount DESC ) AS Rank
                        FROM finalInfo
                        ) rs
                        LEFT JOIN auctions
                        ON auctions.id=rs.auction_id
                        LEFT JOIN users
                        ON users.id=rs.user_id
                        WHERE Rank <= 5
            "));

        foreach ($finishedAuctions as $auction) {
            Mail::to($auction->getSeller()->email)->send(new AuctionEnded($auction->title));
        }
        foreach ($endingAuctions as $auction) {
            Mail::to($auction->email)->send(new AuctionEnding($auction->title, $auction->auction_id));
        }


        $data = [
            'endingAuctionsCount' => count($endingAuctions),
            'finishedAuctionsCount' => count($finishedAuctions)
        ];
        return view("auctions.finishedauctions")->with($data);
    }

    function getLatAndLon($city, $countryCode)
    {
        // $postalCode = str_replace(' ', '', $postalCode);

        $url = 'http://nominatim.openstreetmap.org/search?country=' . $countryCode . '&city=' . $city . '&format=json&limit=1';

        ini_set('user_agent', 'Mozilla/4.0 (compatible; MSIE 6.0)');

        $response = json_decode(file_get_contents($url));

        if (!count($response))
            return ['error' => 'Geen plaats gevonden met deze stad en landcode combinatie.'];

        $lat = $response[0]->lat;
        $lon = $response[0]->lon;

        return ['lat' => $lat, 'lon' => $lon];
    }

    public function handleIsBlocked(Request $request)
    {
        return view("auctions.blocked");
    }
}
