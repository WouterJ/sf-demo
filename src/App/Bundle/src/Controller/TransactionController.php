<?php

namespace App\Bundle\Controller;

use App\Bundle\Form\BookTransactionForm;
use App\Bundle\Form\ImportForm;
use App\Finance\Transaction\Command\BookTransaction;
use App\Finance\Transaction\Command\RegisterBankAccount;
use App\Finance\Transaction\Import\ImportedTransaction;
use App\Finance\Wallet\WalletId;
use Money\Currency;
use Money\Money;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class TransactionController extends Controller
{
    /** @Route("/transaction/book", name="book_transaction") */
    public function book(Request $request)
    {
        $form = $this->createForm(BookTransactionForm::class);
        if ($request->query->has('from')) {
            $form->get('from')->setData(new WalletId(Uuid::fromString($request->query->get('from'))));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = new BookTransaction(
                Uuid::uuid4(),
                Money::EUR(intval($form['money']->getData() * 100)),
                $form['from']->getData()->id(),
                $form['to']->getData()->id(),
                $form['date']->getData(),
                $form['description']->getData()
            );

            $this->get('command_bus')->handle($command);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('transaction/book.twig', ['form' => $form->createView()]);
    }

    /** @Route("/transaction/import", name="import_transactions") */
    public function import(Request $request)
    {
        $form = $this->createForm(ImportForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('transaction_file')->getData();


            $transactions = $this->get('app.importer')->parseTransactions(file_get_contents($file->getPathname()));
            dump($this->get('app.guesser')->guessToWallet($transactions[1]));
        }

        return $this->render('transaction/import.twig', ['form' => $form->createView()]);
    }
}
