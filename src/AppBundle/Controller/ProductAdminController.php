<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ProductAdminController extends Controller
{
    /**
     * @Route("/admin/products", name="product_list")
     */
    public function listAction()
    {
        $products = $this->getDoctrine()
            ->getRepository('AppBundle:Product')
            ->findAll();

        return $this->render('product/list.html.twig', [
            'products' => $products
        ]);
    }

    // Setting the route. When this route is hit, it runs this method which ultimately checks to see if the request to this URL was a POST request, if it is then it creates a product, saves it to the database and returns a view.
    // If it is not a post request then it simply just displays the page.
    /**
     * @Route("/admin/products/new", name="product_new")
     */
    public function newAction(Request $request)
    {
        if ($request->isMethod('POST')) { // if the request is a POST request
            $this->addFlash('success', 'Product created FTW!'); // Displays a flash message to the user saying that the product has been created

            $product = new Product(); // Creating a new product object
            $product->setName($request->get('name')); // All these setters are used to get what the user has filled out a specific field on the page
            $product->setDescription($request->get('description'));
            $product->setPrice($request->get('price'));
            $product->setAuthor($this->getUser());

            $em = $this->getDoctrine()->getManager(); // Getting the doctrine entity manager so that we can use persist() and flush() methods to save the product to the database
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_list'); // Once it has been created and saved to the database, the user is redirected to the product list view
        }

        return $this->render('product/new.html.twig'); // If the request is not a POST request, then just display the
    }

    /**
     * @Route("/admin/products/delete/{id}", name="product_delete")
     * @Method("POST")
     */
    public function deleteAction(Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($product);
        $em->flush();

        $this->addFlash('success', 'The product was deleted');

        return $this->redirectToRoute('product_list');
    }
}
