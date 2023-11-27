<?php

use deemru\Cloaked;

require __DIR__ . '/../vendor/autoload.php';

if( !function_exists( 'random_bytes' ) ){ function random_bytes( $size ){ $rnd = ''; while( $size-- ) $rnd .= chr( mt_rand() ); return $rnd; } }

$cloaked = new Cloaked();
$sensitive = 'Hello, world!';
$cloaked->cloak( $sensitive );
$cloaked->uncloak( function( $data ){ /*do stuff with*/ $data; } );

class tester
{
    private $successful = 0;
    private $failed = 0;
    private $depth = 0;
    private $info = [];
    private $start = [];
    private $init;

    public function pretest( $info )
    {
        $this->info[$this->depth] = $info;
        $this->start[$this->depth] = microtime( true );
        if( !isset( $this->init ) )
            $this->init = $this->start[$this->depth];
        $this->depth++;
    }

    private function ms( $start )
    {
        $ms = ( microtime( true ) - $start ) * 1000;
        $ms = $ms > 100 ? round( $ms ) : $ms;
        $ms = sprintf( $ms > 10 ? ( $ms > 100 ? '%.00f' : '%.01f' ) : '%.02f', $ms );
        return $ms;
    }

    public function test( $cond )
    {
        $this->depth--;
        $ms = $this->ms( $this->start[$this->depth] );
        echo ( $cond ? 'SUCCESS: ' : 'ERROR:   ' ) . "{$this->info[$this->depth]} ($ms ms)\n";
        $cond ? $this->successful++ : $this->failed++;
    }

    public function finish()
    {
        $total = $this->successful + $this->failed;
        $ms = $this->ms( $this->init );
        echo "  TOTAL: {$this->successful}/$total ($ms ms)\n";
        sleep( 3 );

        if( $this->failed > 0 )
            exit( 1 );
    }
}

function searchInDump( $t, $target, $data )
{
    if( PHP_OS === 'WINNT' )
    {
        $t->pretest( 'searchInDump( ' . $target . ' )' );
        {
            exec( 'procdump -ma ' . getmypid() . ' -o phpdump' );
            $dmp = file_get_contents( 'phpdump.dmp' );
            $offset = 0;
            $count = 0;
            for( ;; )
            {
                $offset = strpos( $dmp, $data, $offset );
                if( $offset === false )
                    break;
                ++$offset;
                ++$count;
            }
        }
        $t->test( $target === $count );
    }
}

echo "   TEST: Cloaked\n";
$t = new tester();
$cloaked = new Cloaked;
$sensitiveHolder = random_bytes( 32 );
$sensitiveHolder2 = random_bytes( 32 );

$t->pretest( 'cloak' );
{
    $sensitive = $sensitiveHolder;
    $cloaked->cloak( $sensitive );
    $t->test( $sensitive === null );
}

searchInDump( $t, 1, $sensitiveHolder );

$t->pretest( 'uncloak' );
{
    $cloaked->uncloak( function( $data ) use ( &$sensitive ){ $sensitive = $data; } );
    $t->test( $sensitive === $sensitiveHolder );
}

searchInDump( $t, 2, $sensitiveHolder );

$t->pretest( 'cloak variant' );
{
    $sensitive2 = $sensitiveHolder2;
    $cloaked->cloak( $sensitive2, '2' );
    $t->test( $sensitive2 === null );
}

searchInDump( $t, 1, $sensitiveHolder2 );

$t->pretest( 'uncloak' );
{
    $cloaked->uncloak( function( $data ) use ( &$sensitive2 ){ $sensitive2 = $data; }, '2' );
    $t->test( $sensitive2 === $sensitiveHolder2 );
}

searchInDump( $t, 2, $sensitiveHolder );

$cloaked->cloak( $sensitive, 'a' );
$cloaked->cloak( $sensitive2, 'b' );
$cloaked->cloak( $sensitiveHolder, 'c' );
$cloaked->cloak( $sensitiveHolder2, 'd' );

$cloaked->uncloak( function( $data ) use ( $t ){ searchInDump( $t, 1, $data ); }, 'a' );
$cloaked->uncloak( function( $data ) use ( $t ){ searchInDump( $t, 1, $data ); }, 'b' );
$cloaked->uncloak( function( $data ) use ( $t ){ searchInDump( $t, 1, $data ); }, 'c' );
$cloaked->uncloak( function( $data ) use ( $t ){ searchInDump( $t, 1, $data ); }, 'd' );

$t->finish();
