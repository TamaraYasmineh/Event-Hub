<?php

namespace App\Http\Controllers;

use App\Models\Add;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddsController extends Controller
{
    public function createAdds(Request $request)
    {
        $validate = $request->validate([
            'link' => ['required', 'url'],
            'NameOfTheOwnerCompany' => ['required', 'string'],
            'viewPlace' => ['required'],
            'startDate' => ['required', 'date', 'date_format:d-m-Y'],
            'endDate' => ['required', 'date', 'date_format:d-m-Y'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

        ]);
        $validate['image'] = $request->file('image')->store('addsImage', 'public');
        $validate['active'] = 'activated';
        $adds = Add::create($validate);
        return response()->json([
            'message' => 'adds added successfully',
            'adds' => $adds
        ], 201);
    }


    public function clickUnActivatedAdds($id)
    {
        $add = Add::find($id);

        if (!$add) {
            return response()->json([
                'message' => 'add not found'

            ], 404);
        }
        if ($add->active === 'activated') {
            $add->active = 'notActivated';
            $add->save();
            return response()->json([
                'message' => 'add active updated successfully'
            ], 201);
        }
        return response()->json([
            'message' => 'add already not activated',
        ], 201);
    }

    public function clickActivatedAdds($id)
    {
        $add = Add::find($id);

        if (!$add) {
            return response()->json([
                'message' => 'add not found'

            ], 404);
        }
        if ($add->active === 'notActivated') {
            $add->active = 'activated';
            $add->save();
            return response()->json([
                'message' => 'add active updated successfully'
            ], 201);
        }
        return response()->json([
            'message' => 'add already activated',
        ], 201);
    }

    public function showAddsForAdmins()
    {
        $adds = Add::select('id', 'link', 'image', 'NameOfTheOwnerCompany', 'startDate', 'endDate', 'viewPlace', 'active', 'hits')
            ->orderByRaw("FIELD(viewPlace, 'gold', 'silver', 'bronze')")
            ->latest()
            ->get();
        return response()->json([
            "adds" => $adds
        ]);
    }
    // تابع showAddsForAdmins سيعرض هذا الرد وهو عبارة عن جدول يعرض بداخله جميع الاعلانات  بالمعلومات التالية وبداخل هذا الجدول نريد ان نضع ايضا خانة للأزرار
    // (بعد هذا التابع )حيث سيكون بالخانة زر تفعيل الحدث والغاء تفعيل الحدث(التوابع السابقة) وزر لحذف الحدث نهائيا

    //   "adds": [
    //     {
    //         "id": 21,
    //         "link": "https://forms.gle/BaiKwntWdmZS9svj6",
    //         "image": "addsImage/P4NLQkJq4eILReMDuIv38UQmhthedaM1trGVYT4R.jpg",
    //         "NameOfTheOwnerCompany": "شركة أصدقاء وتار",
    //         "startDate": "2025-11-19",
    //         "endDate": "2025-11-19",
    //         "viewPlace": "gold",
    //         "active": "activated",
    //         "hits": 0
    //     },
    //     {
    //         "id": 19,
    //         "link": "https://forms.gle/BaiKwntWdmZS9svj6",
    //         "image": "addsImage/PZGqTVvOqkqm76tFRDLzhVUW2EhUPgJkC9JCOMic.jpg",
    //         "NameOfTheOwnerCompany": "شركة الأنظمة المتجددة",
    //         "startDate": "2025-11-12",
    //         "endDate": "2025-11-26",
    //         "viewPlace": "gold",
    //         "active": "notActivated",
    //         "hits": 0
    //     },
    //     {
    //         "id": 18,
    //         "link": "https://forms.gle/BaiKwntWdmZS9svj6",
    //         "image": "addsImage/GXZ3pAOYnHjKiSJS2oZ3OFzo6cz5DnrbGGXxsSoI.jpg",
    //         "NameOfTheOwnerCompany": "شركة احمد",
    //         "startDate": "2025-11-06",
    //         "endDate": "2025-11-27",
    //         "viewPlace": "gold",
    //         "active": "notActivated",
    //         "hits": 0
    //     },
    //     {
    //         "id": 15,
    //         "link": "https://forms.gle/BaiKwntWdmZS9svj6",
    //         "image": "http://localhost/eventhubwithfront/public/storage/addsImage/rgTz9zgsh3KLH5c0XMpOb9gi9yOHYGEAo2zEKOkV.jpg",
    //         "NameOfTheOwnerCompany": "شركة البيش",
    //         "startDate": "2025-11-14",
    //         "endDate": "2025-11-27",
    //         "viewPlace": "gold",
    //         "active": "activated",
    //         "hits": 0
    //     },

    public function destroy($id)
    {
        $add = Add::find($id);

        if (!$add) {
            return response()->json(["error" => "الإعلان غير موجود"]);
        }

        $user = Auth::user();
        if (!$user->hasRole(['admin', 'superadmin'])) {
            return response()->json(["error" => "ليس لديك صلاحية لحذف الإعلان"]);
        }

        if ($add->image && file_exists(public_path('storage/addsImage/' . basename($add->image)))) {
            unlink(public_path('storage/addsImage/' . basename($add->image)));
        }

        $add->delete();

        return response()->json(["error" => "تم حذف الإعلان بنجاح"]);
    }
}
