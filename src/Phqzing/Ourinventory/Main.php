<?php

namespace Phqzing\Ourinventory;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\command\{CommandSender, Command};
use pocketmine\utils\TextFormat as TE;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;

class Main extends PluginBase implements Listener{
  
  
  public $oldinv = [];
  public $oldarmor = [];
  
  
  public function onEnable():void{
    @mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $this->getResource("config.yml");
    $this->getLogger()->info("You are now ready to ste- I mean copy inventories.");
  }
  
  
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool{
    switch($command->getName()){
      case "copyinventory":
        if($sender instanceof Player){
          if($sender->hasPermission($this->getConfig()->get("permission"))){
            if(isset($args[0])){
              $player = $this->getServer()->getPlayer($args[0]);
              if($player instanceof Player){
                if(isset($this->oldinv[$sender->getName()])){
                  unset($this->oldinv[$sender->getName()]);
                  unset($this->oldarmor[$sender->getName()]);
                }
                $newinv = $player->getInventory()->getContents();
                $newarmor = $player->getArmorInventory()->getContents();
                
                $oldinv = $sender->getInventory()->getContents();
                $oldarmor = $sender->getArmorInventory()->getContents();
                
                $this->oldinv[$sender->getName()] = $oldinv;
                $this->oldarmor[$sender->getName()] = $oldarmor;
                
                $sender->getInventory()->clearAll();
                $sender->getArmorInventory()->clearAll();
                $sender->getInventory()->setContents($newinv);
                $sender->getArmorInventory()->setContents($newarmor);
                $sender->sendMessage(TE::GREEN."You have stol- I mean ".TE::ITALIC."borrowed ".TE::RESET.TE::GRAY.$player->getName().TE::GREEN."'s inventory.");
                
                if($this->getConfig()->get("get-oldinv-back") === true){
                  $timer = gmdate('i:s', $this->getConfig()->get("oldinv-timer"));
                  $sender->sendMessage(TE::DARK_AQUA."You have ".TE::YELLOW.$timer.TE::DARK_AQUA." before your old armor and inventory disappears, to get them back type: ".TE::RED."/getoldinventory".TE::DARK_AQUA.".");
                  $this->getScheduler()->scheduleRepeatingTask(new OldInventoryTask($this, $sender->getName(), $this->getConfig()->get("oldinv-timer")), 20);
                }
              }
            }else{
              $sender->sendMessage("Usage: /copyinventory [player]");
            }
          }else{
            $sender->sendMessage(TE::RED."You have no permission to use this command.");
          }
        }else{
          $sender->sendMessage("You can't use this command in console.");
        }
      break;
      
      case "getoldinventory":
        if($sender instanceof Player){
          if($sender->hasPermission($this->getConfig()->get("permission"))){
            if(isset($this->oldinv[$sender->getName()])){
              $sender->getInventory()->clearAll();
              $sender->getArmorInventory()->clearAll();
              $sender->getInventory()->setContents($this->oldinv[$sender->getName()]);
              $sender->getArmorInventory()->setContents($this->oldarmor[$sender->getName()]);
              unset($this->oldinv[$sender->getName()]);
              unset($this->oldarmor[$sender->getName()]);
              $sender->sendMessage(TE::GREEN."Your inventory is now back to its original state.");
            }else{
              $sender->sendMessage(TE::GOLD."You don't have any old inventories.");
            }
          }else{
            $sender->sendMessage(TE::RED."You have no permission to use this commnad.");
          }
        }else{
          $sender->sendMessage("You can't use this command in console.");
        }
      break;
    }
    return true;
  }
  
  public function onQuit(PlayerQuitEvent $ev){
    $player = $ev->getPlayer();
    unset($this->oldinv[$player->getName()]);
    unset($this->oldarmor[$player->getName()]);
  }
}
