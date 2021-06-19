<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PurchasePaymentSuccessController extends AbstractController
{
    /** 
     * @Route("/purchase/terminate/{id}", name="purchase_payment_success")
     *@IsGranted("ROLE_USER")
     */
    public function success($id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em, CartService $cartService): Response
    {
        // 1 . Je recupère la commande
        $purchase = $purchaseRepository->find($id);

        if (
            !$purchase ||
            ($purchase && $purchase->getUser() !== $this->getUser()) ||
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {
            $this->addFlash("warning", "La commande n'existe pas");
            return $this->redirectToRoute("purchase_index");
        }

        // 2. Je la fait passer au status PAYEE (PAID)
        $purchase->setStatus(Purchase::STATUS_PAID);
        $em->flush();
        // 3 . Je vide le panier
        $cartService->empty();
        // 4. Je redirige avec un flash vers la liste des commande
        $this->addFlash('success', "La commande a été payée et confirmée !");
        return $this->redirectToRoute("purchase_index");
    }
}