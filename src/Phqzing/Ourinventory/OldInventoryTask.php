<?php

namespace Phqzing\OurInventory;

use pocketmine\scheduler\Task;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TE;

class OldInventoryTask extends Task {
  
  private $plugin;
  private $player;
  
  private $time;
  
  public function __construct(Main $plugin, $username, int $time){
    $this->plugin = $plugin;
    $this->player = $username;
    $this->time = $time;
  }
  
  
  public function onRun(int $tick){
    $player = $this->plugin->getServer()->getPlayerExact($this->player);
    if($player instanceof Player){
      if(isset($this->plugin->oldinv[$player->getName()])){
        if($this->time == 0){
          unset($this->plugin->oldinv[$player->getName()]);
          unset($this->plugin->oldarmor[$player->getName()]);
          $player->sendMessage(TE::RED."[Expired] ".TE::GOLD."You can no longer get your old inventory and armor back.");
          $this->plugin->getScheduler()->cancelTask($this->getTaskId());
        }
      }else{
        $this->plugin->getScheduler()->cancelTask($this->getTaskId());
      }
    }else{
      $this->plugin->getScheduler()->cancelTask($this->getTaskId());
    }
  }
}
