<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Book;
use File;
use JWTAuth;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $books = Book::all();
        return response()->json(array('success' => true, 'books' => $books), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'book_title'=>'required',
            'description'=>'required',
            'price'=>'required|numeric',
            'book_image'=>'image',
            'status'=>'required|numeric'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        if ($request->hasFile('book_image')) {
            $file = $request->file('book_image');
            $file_name = time().'.'.$file->extension();
            $path = public_path('/book_images/');
            $file->move($path,$file_name);
        }else{
            $file_name = 'no-image.png';
        }
        $book = new Book([
            'book_title' => $request->get('book_title'),
            'description' => $request->get('description'),
            'price' => $request->get('price'),
            'book_image' => $file_name,
            'status' => $request->get('status')
        ]);
        $book->save();
        return response()->json(array('success' => true, 'last_insert_id' => $book->id), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $book = Book::find($id);
        $book->book_image = asset('/book_images').'/'.$book->book_image;
        $book_user = $book->user();
        return response()->json(array('success' => true, 'book' => $book, 'book_user'=>$book_user), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'book_title'=>'required',
            'description'=>'required',
            'price'=>'required|numeric',
            'book_image'=>'image',
            'status'=>'required|numeric'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        
        $book = Book::find($id);
        if($request->get('status') == 1){
            $book->user_id = null;
        }
        if ($request->hasFile('book_image')) {
            $file = $request->file('book_image');
            $file_name = time().'.'.$file->extension();
            $path = public_path('/book_images/');
            $file->move($path,$file_name);
            $book->book_image = $file_name;
        }
        $book->book_title = $request->get('book_title');
        $book->description = $request->get('description');
        $book->price = $request->get('price');
        $book->status = $request->get('status');
        $book->save();  
        return response()->json(array('success' => true, 'updated_book' => $book), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $book = Book::find($id);
        $file_path = public_path('/book_images/').$book->book_image;
        if($book->book_image != 'no-image.png' && File::exists($file_path)) File::delete($file_path);
        $book->delete();
        return response()->json(array('success' => true), 200);
    }

    public function rent_book(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $userId = $user->id;
        $id = $request->book_id;

        $book = Book::find($id);
        if($book->status == 1){
            $book->status = 0;
            $book->user_id = $userId;
            $book->save();
            return response()->json(array('success' => true, 'message'=> 'The book is been given on rent to you.','book'=>$book), 200);
        }else{
            return response()->json(array('success' => false, 'message'=> 'Sorry! The book you are looking for is not available.'), 200);
        }
    }

    public function return_book(Request $request){
        $id = $request->book_id;
        $book = Book::find($id);
        $book->status = 1;
        $book->user_id = null;
        $book->save();
        return response()->json(array('success' => true, 'message' => 'Thank you! Book has been returned. You can look for another book.','returned_book'=>$book), 200);
    }
}
