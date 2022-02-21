<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Collection;
use App\Models\Card;
use App\Models\CardCollection;
use App\Models\CardOnSale;

class CardsController extends Controller
{
    public function registerCards(Request $req)
    {

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
        } else {

            $data = $req->getContent();
            $data = json_decode($data);

            if ($req->user->rol == 'administrator') {  //si es administrador, puede dar de alta cartas

                $collection = Collection::where("id", "=", $data->collection)->first();

                if ($collection) {
                    $card = new Card();
                    $card->name = $data->name;
                    $card->description = $data->description;

                    try {
                        $card->save();
                        //creo una nueva carta-colección y asocio el id de la tabla intermedia al id de cartas y al id de colección
                        $cardCollection = new CardCollection();
                        $cardCollection->cards_id = $card->id;
                        $cardCollection->collections_id = $collection->id;
                        $cardCollection->save();

                        $response['msg'] = "Carta guardada con id " . $card->id;
                    } catch (\Exception $e) {
                        $response['status'] = 0;
                        $response['msg'] = "Se ha producido un error: " . $e->getMessage();
                    }
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "No existe esta colección";
                }
            }
        }
        return response()->json($response);
    }

    public function registerCollections(Request $req)
    {

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
        } else {

            $data = $req->getContent();
            $data = json_decode($data);
            $validId = [];

            if ($req->user->rol == 'administrator') { //si es administrador, puede dar de alta colecciones

                foreach ($data->cards as $addedCard) {
                    if (isset($addedCard->id)) {
                        $card = Card::where('id', '=', $addedCard->id)->first();
                        if ($card) {
                            array_push($validId, $card->id); //añadimos dicha carta válida al array
                        }
                    } elseif (isset($addedCard->name) && isset($addedCard->description)) {
                        $newCard = new Card();
                        $newCard->name = $addedCard->name;
                        $newCard->description = $addedCard->description;
                        $newCard->save();

                        try {
                            array_push($validId, $newCard->id);
                            $response['msg'] = "Carta guardada con id " . $newCard->id;
                        } catch (\Exception $e) {
                            $response['status'] = 0;
                            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
                        }
                    } else {
                        $response['status'] = 0;
                        $response['msg'] = "Los datos de la carta introducidos no son correctos";
                    }
                }

                if (!empty($validId)) {
                    $cardsID = implode(", ", $validId); //convertimos el array en una cadena de texto
                    try {
                        $collection = new Collection();
                        $collection->name = $data->name;
                        $collection->simbol = $data->simbol;
                        $collection->edition_date = $data->edition_date;
                        $collection->save();

                        foreach ($validId as $id) {
                            $cardCollection = new CardCollection();
                            $cardCollection->cards_id = $id;
                            $cardCollection->collections_id = $collection->id;
                            $cardCollection->save();
                        }
                        $response['msg'] = "Colección creada con las siguientes cartas agregadas (ID): " . $cardsID;
                    } catch (\Exception $e) {
                        $response['status'] = 0;
                        $response['msg'] = "Se ha producido un error: " . $e->getMessage();
                    }
                }
            }
        }
        return response()->json($response);
    }

    public function searchCards(Request $req)
    {
        $response = ['status' => 1, "msg" => ""];

        $data = $req->getContent();
        $data = json_decode($data);

        try {
            if (Card::where('name', 'like', '%' . $req->input('name') . '%')->exists()) {
                if ($req->user->rol == 'professional' || $req->user->rol == 'particular') {
                    $card = Card::select('id', 'name', 'description')
                        ->where('name', 'like', '%' . $req->input('name') . '%')
                        ->get();
                    $response['msg'] = $card;
                }
            } else {
                $response['status'] = 0;
                $response['msg'] = "No existe esta carta, prueba de nuevo";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
        }
        return response()->json($response);
    }

    public function sellCards(Request $req)
    {
        $response = ['status' => 1, "msg" => ""];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'card_id' => 'required|integer',
            'quantity' => 'required|integer',
            'total_price' => 'required|numeric|min:0|not_in:0' //no tiene en cuenta comenzados en 0. y el valor mínimo es 0
        ]);

        if ($validator->fails()) {
            $response['status'] = 0;
            $response['msg'] = $validator->errors();
        } else {

            $data = $req->getContent();
            $data = json_decode($data);

            if ($req->user->rol == 'professional' || $req->user->rol == 'particular') {
                $card = Card::where('id', '=', $data->card_id)->first();  //Encontrar carta por id
                if ($card) {
                    $cardOnSale = new CardOnSale();
                    $cardOnSale->card_id = $data->card_id;
                    $cardOnSale->quantity = $data->quantity;
                    $cardOnSale->total_price = $data->total_price;
                    $cardOnSale->seller_id = $req->user->id;
                    try {
                        $cardOnSale->save();
                        $response['msg'] = "Carta/s vendida/s";
                    } catch (\Exception $e) {
                        $response['status'] = 0;
                        $response['msg'] = "Se ha producido un error: " . $e->getMessage();
                    }
                } else {
                    $response['status'] = 0;
                    $response['msg'] = "No existe esta carta o el id es incorrecto";
                }
            }
        }
        return response()->json($response);
    }

    public function buyCards(Request $req)
    {

        $response = ['status' => 1, "msg" => ""];

        $data = $req->getContent();
        $data = json_decode($data);

        try {
            if ($req->has('name')) {
                $cardOnSale = CardOnSale::join('cards', 'cards.id', '=', 'cards_sale.card_id')
                    ->join('users', 'users.id', '=', 'cards_sale.seller_id')
                    ->where('cards.name', 'like', '%' . $req->input('name') . '%')
                    ->select('cards.name', 'cards_sale.quantity', 'cards_sale.total_price', 'users.username')
                    ->orderBy('cards_sale.total_price', 'ASC')
                    ->get();
                $response['msg'] = $cardOnSale;
            } else {
                $response['status'] = 0;
                $response['msg'] = "No existe esta carta, prueba de nuevo";
            }
        } catch (\Exception $e) {
            $response['status'] = 0;
            $response['msg'] = "Se ha producido un error: " . $e->getMessage();
        }
        return response()->json($response);
    }
}
