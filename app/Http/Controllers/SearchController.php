<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\User;

class SearchController extends Controller
{
    /**
     * Return
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uni(Request $request)
    {   
        // Validate search query
        $data = request(['uni_name']);

        $validator = Validator::make($data, [
            'uni_name' => ['required', 'string', 'max:255'],
        ]);
        
        if ($validator->fails()) {
            return response()->json(['success' => false, 'error' => $validator->messages()]);
        }

        // searched name
        $name = $request["uni_name"];

        //  return json data of universities
        $path = storage_path() . "/app/world_universities_and_domains.json";
        $json = json_decode(file_get_contents($path), true);

        // init possible match
        $matched_uni = null;

        // Search for match
        for($i = 0; $i < count($json); $i++){
            // will return first found result of searched name
            if( strpos(strtolower($json[$i]["name"]), strtolower($name)) !== false ){
                $matched_uni = $json[$i];
                break;
            }
        }

        return response()->json($matched_uni, 200);
    }

    public function users(Request $request){

        $user = auth()->user();

        // [University, Mentoring, Mentored in]
        $search_params = [$user->uni_name, $user->mentor_subject, $user->mentee_subject];

        // Return users that want to be mentored in the subject the searching user is mentoring &&
        // users that want to mentor in subjects the searching user would like to be mentored in.
        $matched_users = User::where('uni_name', 'LIKE', '%'.$search_params[0].'%')
            ->where('id', '!=', $user->id)->where('mentor_subject', 'LIKE', $search_params[2])
            ->orWhere('mentee_subject', 'LIKE', $search_params[1])->get();

        if(strlen($search_params[0]) < 1){
            return response()->json(["error" => "University name not given"], 404);
        } else if (count($matched_users) < 1){
            return response()->json(["error" => "No matched students"], 404);
        }

        return response()->json(["success" => $matched_users], 200);
    }
}
