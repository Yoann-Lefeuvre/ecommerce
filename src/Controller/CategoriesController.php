<?php

namespace App\Controller;


use App\Entity\Categories;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
 

    #[Route('/{slug}', name:'list')]
    public function list(Categories $category): Response
    {
        // On va chercher la liste des produits de la categorie
        $products = $category->getProducts();

        // dd($product);
        return $this->render('categories/list.html.twig', ['categorie' => $category, 'products'=> $products]); 
        // equivalent à:  return $this->render('categories/list.html.twig', compact('category','products'));
    }
}