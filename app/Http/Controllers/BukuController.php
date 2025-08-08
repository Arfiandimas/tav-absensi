<?php

namespace App\Http\Controllers;

use App\Http\Requests\BukuRequest;
use App\Services\Buku\AddUpdateBukuService;
use App\Services\Buku\DeleteBukuService;
use App\Services\Buku\GetBukuService;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $books = (new GetBukuService())->call();
        if (!$books->status()) {
            return redirect()->back()->with(['status'=> $books->state(), 'message'=> $books->message()]);
        }
        $books = $books->result();
        return view('buku.index', compact('books'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('buku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BukuRequest $request)
    {
        $book = (new AddUpdateBukuService($request))->call();
        if (!$book->status()) {
            return redirect()->back()->with(['status'=> $book->state(), 'message'=> $book->message()]);
        }
        return redirect()->route('buku.show', $book->result())->with(['status'=> $book->state(), 'message'=> $book->message()]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $book = (new GetBukuService())->setId((int)$id)->call();
        if (!$book->status()) {
            return redirect()->back()->with(['status'=> $book->state(), 'message'=> $book->message()]);
        }
        $book = $book->result();
        return view('buku.show', compact('book'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $book = (new GetBukuService())->setId((int)$id)->call();
        if (!$book->status()) {
            return redirect()->back()->with(['status'=> $book->state(), 'message'=> $book->message()]);
        }
        $book = $book->result();
        return view('buku.edit', compact('book'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BukuRequest $request, string $id)
    {
        $book = (new AddUpdateBukuService($request))->setId($id)->call();
        if (!$book->status()) {
            return redirect()->back()->with(['status'=> $book->state(), 'message'=> $book->message()]);
        }
        return redirect()->route('buku.show', $book->result())->with(['status'=> $book->state(), 'message'=> $book->message()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $book = (new DeleteBukuService($id))->call();
        if (!$book->status()) {
            return redirect()->back()->with(['status'=> $book->state(), 'message'=> $book->message()]);
        }
        return redirect()->route('buku.index')->with(['status'=> $book->state(), 'message'=> $book->message()]);
    }
}
