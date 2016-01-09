<?php

namespace PP\RequestBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PP\RequestBundle\Entity\Tag;
/**
 * Description of LoadCategory
 *
 * @author Olivier
 */
class aa_LoadTag implements FixtureInterface{
    
  public function load(ObjectManager $manager)
  {
    
    $names = array('iconostas', 'intwist', 'vesta', 'maladminister', 'spongier', 'forninst', 'censoriousness', 'luminance', 'lakeland', 'gobbing', 'premating', 'didachographer', 'uniteable', 'tableting', 'decampment', 'zwieback', 'peptize', 'redriving', 'mislabel', 'latinised', 'gandhiist', 'loaning', 'cabriolet', 'nonnebular', 'rainbird', 'mariolatrous', 'finalized', 'prestimulated', 'pectination', 'requalification', 'ovariotomist', 'skilfulness', 'ingraft', 'embezzle', 'wayleave', 'salutary', 'trajan', 'hay', 'keijo', 'unified', 'negrophil', 'chippeway', 'sarcoenchondromata', 'superintolerableness', 'void', 'gastroenterologic', 'tehuelche', 'commemoration', 'contentional', 'laghouat', 'countercharge', 'gibeonite', 'profligate', 'squillageeing', 'floorer', 'trainable', 'sassaby', 'worminess', 'lophophoral', 'versatile', 'shipman', 'forlornly', 'trihedral', 'provencal', 'megadontism', 'bruteness', 'imprecision', 'perplexed', 'peso', 'subheadquarters', 'bamako', 'cyperaceous', 'aloeus', 'echo', 'bejel', 'unbranded', 'citronellol', 'calakmul', 'interstriving', 'dilatation', 'ancientness', 'interspersedly', 'bubble', 'snaglike', 'acropathy', 'aerodyne', 'intercompany', 'fineableness', 'sensitization', 'guaguanche', 'polytheistically', 'subtower', 'patrilinearly', 'instilled', 'petrodollar', 'calculate', 'swabbing', 'refired', 'parade', 'skean');

    foreach ($names as $name) {
     
      $tag = new Tag();
      $tag->setName($name);
      
      $manager->persist($tag);
    }
    
    $manager->flush();
  }
   
  
}
