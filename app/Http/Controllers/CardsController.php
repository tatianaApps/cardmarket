<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Collection;
use App\Models\Card;
use App\Models\CardCollection;

class CardsController extends Controller
{
    public function registerCards(Request $req){
        
        $response = ['status' => 1, "msg" => ""];
  
        $validator = Validator::make(json_decode($req->getContent(), true), [
            'name' => 'required|max:50',
            'description' => 'required|max:300',
            'collection' => 'required|integer',
        ]);

        if ($validator->fails()) {
            //Preparar la respuesta 
            $response['status'] = 0;
    		$response['msg'] = $validator->errors();
            return response()->json($response);
        }else {

            $data = $req->getContent();
            $data = json_decode($data);

            if($req->user->rol == 'administrator'){  //si es administrador, puede dar de alta cartas

                $collection = Collection::where("id", "=", $data->collection)->first();
            
                if ($collection) {
                    $card = new Card();
                    $card->name = $data->name;
                    $card->description = $data->description;
                     
                    try {
                        $card->save();  
                        //creo una nueva carta-colección y asocio el id de la tabla intermedia al id de cartas y al id de colección
                        $cardCollection = new CardCollection();
                        $cardCollection->card_id = $card->id;
                        $cardCollection->collection_id = $collection_id;
                        $cardCollection->save();

                        $response['msg'] = "Carta guardada con id ".$card->id;

                    }catch(\Exception $e){
                        $response['status'] = 0;
                        $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                    }
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "No existe esta colección";
                }        
            } 
        }
        return response()->json($response);
    }

    public function registerCollections(Request $req){

        $response = ['status' => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'name' => 'required|max:50',
            'simbol' => 'required|max:100',
            'edition_date' => 'required|date',
            'cards' => 'required'
        ]);

        if ($validator->fails()) {
            //Preparar la respuesta 
            $response['status'] = 0;
    		$response['msg'] = $validator->errors();
            return response()->json($response);

        }else {

            $data = $req->getContent();
    	    $data = json_decode($data);
            $validId = [];

            foreach ($data->cards as $addedCard) {
                $response['msg'] = "Fuera del IF";
                if(isset($addedCard->id)){
                    $response['msg'] = "helloooo";
                    $card = Card::where('id','=',$addedCard->id)->first();
                    if($card){
                        array_push($validId, $card->id); //añadimos dicha carta válida al array
                    }
                    elseif(isset($addedCard->name) && isset($addedCard->description)) {
                        if($req->user->rol == 'administrator'){  //si es administrador, puede dar de alta colecciones
                                
                            $newCard = new Card();
                            $newCard->name = $addedCard->name;
                            $newCard->description = $addedCard->description;
                            $newCard->save();
                            
                            try {
                                array_push($validId, $newCard->id);
                                $response['msg'] = "Carta guardada con id ".$newCard->id;
                                
                            }catch(\Exception $e){
                                $response['status'] = 0;
                                $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                            }  
                        } 
                    }else {
                        $response['status'] = 0;
                        $response['msg'] = "Los datos de la carta introducidos no son correctos";
                    }
                }

                if(!empty($validId)) {
                    $cardsID = implode (", ", $validId); //convertimos el array en una cadena de texto
                    try {
                        $collection = new Collection();
                        $collection->name = $data->name;
                        $collection->simbol = $data->simbol;
                        $collection->edition_date = $data->edition_date;
                        $collection->save();

                        foreach($validId as $id){
                            $cardCollection = new CardCollection();
                            $cardCollection->card_id = $id;
                            $cardCollection->collection_id = $collection->id;
                            $cardCollection->save();
                        }
                        $response['msg'] = "Colección creada con las siguientes cartas agregadas (ID): ".$cardsID;
                        
                    }catch(\Exception $e){
                        $response['status'] = 0;
                        $response['msg'] = "Se ha producido un error: ".$e->getMessage();
                    } 
                }
            }
            return response()->json($response);
        }
    }
}
