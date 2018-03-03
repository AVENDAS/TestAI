<?php



namespace soradore\ai;


/* Base */
use pocketmine\plugin\PluginBase;

/* Events */
use pocketmine\event\Listener;


/* Level and Math */
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

use pocketmine\scheduler\PluginTask;

use pocketmine\entity\Entity;

class main extends PluginBase implements Listener{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->id = [];
    }

    public function onDamage(\pocketmine\event\entity\EntityDamageEvent $ev){
        $entity = $ev->getEntity();
        if($entity instanceof \pocketmine\entity\Zombie){
            $id = $entity->getId();
            if(!isset($this->id[$id])){
                $zombie = new CustomZombie($entity, $ev->getDamager());
                $task = new ZombieTask($this, $zombie);
                $this->getServer()->getScheduler()->scheduleRepeatingTask($task, 1);
                $this->id[$id] = $task;
            }
        }
    }

    public function onDeath(\pocketmine\event\entity\EntityDeathEvent $ev){
        $entity = $ev->getEntity();
        $id = $entity->getID();
        if(isset($this->id[$id])){
            $this->getServer()->getScheduler()->cancelTask($this->id[$id]->getTaskId());
            unset($this->id[$id]);
        }
    }
}





class ZombieTask extends PluginTask{

    public function __construct(PluginBase $plugin, CustomZombie $zombie){
        $this->zombie = $zombie;
        parent::__construct($plugin);
    }

    public function onRun(int $currentTick){
        $target = $this->zombie->getTarget();
        $level = $this->zombie->getLevel();
        if($target == NULL) return;

        $tx = $target->x;
        $tz = $target->z;

        $cx = $this->zombie->getX();
        $cz = $this->zombie->getZ();

        if($cx < 0){
            $x = $tx + $cx;
        }else{
            $x = $tx - $cx;
        }

        if($cz < 0){
            $z = $tz + $cz;
        }else{
            $z = $tz - $cz;
        }

        $rad = atan2($x, $z);

        $x = CustomZombie::SPEED * sin($rad);
        $y = 0;
        $z = CustomZombie::SPEED * cos($rad);

        if($level->getBlockAt(ceil($cx + $x), ceil($this->zombie->getY()), ceil($cz + $z))->getId() !== Block::AIR){
            $y = 1.5;
        }
        $this->zombie->move($x, $y, $z);
        $this->zombie->setYaw(-rad2deg($rad));

    }
}
