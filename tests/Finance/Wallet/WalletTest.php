<?php

namespace App\Finance\Wallet;

use App\Finance\Transaction\Transaction;
use Money\Money;

/**
 * @author Wouter J <wouter@wouterj.nl>
 */
class WalletTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingAnOwnedWallet()
    {
        $wallet = Wallet::ours(1, 'Income', Money::EUR(0));

        $this->assertTrue($wallet->isOurs());
        $this->assertTrue($wallet->id()->equals(new WalletId(1)));
        $this->assertEquals('Income', $wallet->name());
        $this->assertEquals(Money::EUR(0), $wallet->money());
    }

    public function testCreatingANotOwnedWallet()
    {
        $wallet = Wallet::theirs(1, 'Supermarket', Money::EUR(0));

        $this->assertFalse($wallet->isOurs());
        $this->assertTrue($wallet->id()->equals(new WalletId(1)));
        $this->assertEquals('Supermarket', $wallet->name());
        $this->assertEquals(Money::EUR(0), $wallet->money());
    }

    public function testPayingTransactionsFromWallet()
    {
        $wallet = Wallet::ours(1, 'Income', Money::EUR(100000));

        $this->assertEquals(Money::EUR(100000), $wallet->money());

        $wallet->bookCredit($this->createTransaction(Money::EUR(40000)));

        $this->assertEquals(Money::EUR(60000), $wallet->money());
    }

    public function testRecievingMoneyForWallet()
    {
        $wallet = Wallet::ours(1, 'Income', Money::EUR(50000));

        $this->assertEquals(Money::EUR(50000), $wallet->money());

        $wallet->bookDebit($this->createTransaction(Money::EUR(100000)));

        $this->assertEquals(Money::EUR(150000), $wallet->money());
    }

    public function testNegativeMoney()
    {
        $wallet = Wallet::ours(1, 'Income', Money::EUR(0));

        $wallet->bookCredit($this->createTransaction(Money::EUR(5000)));

        $this->assertEquals(Money::EUR(-5000), $wallet->money());
    }

    private function createTransaction(Money $money)
    {
        $transaction = $this->prophesize(Transaction::class);
        $transaction->money()->willReturn($money);

        return $transaction->reveal();
    }
}
