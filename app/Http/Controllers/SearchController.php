<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

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
}
