  //Creates subcategories
  function show_massSubcats($tcg, $category, array $array, $pendname='pending') {
    $database = new Database;
    $sanitize = new Sanitize;
    $tcg = $sanitize->for_db($tcg);
    $category = $sanitize->for_db($category);
    $tcginfo = $database->get_assoc("SELECT * FROM `tcgs` WHERE `name`='$tcg' LIMIT 1");
    $tcgid = $tcginfo['id'];
    $cardsurl = $tcginfo['cardsurl'];
    $format = $tcginfo['format'];
    
    $cards = get_category($tcg, $category); 
    $cards = explode(', ',$cards);
    
    $list = get_additional($tcg, $category);
    if(empty($list)) {
      $list = array();
      foreach($cards as $card) {
        $deck = substr($card,0,-2); 
        $list[] = $deck;
      }
      $list = array_unique($list);
      sort($list);
    } else {
      $list = explode(', ',$list);
    }

    $pend = $database->query("SELECT * FROM `trades` WHERE `tcg`='$tcgid'");
    $pending = array();
    // Gets all pending cards
    if(mysqli_num_rows($pend)>0) {
      while($p=mysqli_fetch_assoc($pend)) {
        if(!empty($p['receivingcat'])) {
          $cats = explode(', ',$p['receivingcat']);
          $divide = explode('; ', $p['receiving']);
          for($i=0;$i<count($cats);$i++) {
            if($cats[$i]===$category) {
              $pending = array_merge($pending, explode(', ',$divide[$i]));
            }
          }
        }
        else {
          $divide = explode(', ', $p['receiving']);
          foreach($divide as $pendcard) {
            if(in_array(substr($pendcard,0,-2),$list)) {
              $pending[] = $pendcard;
            }
          }
        }
      }
    } 
    array_walk($pending,'trim_value');
    //Makes a deck array with cards.
    $cards = array_unique($cards);
    $cards = array_combine($cards, $cards);
    //Adds related pending cards to category.
    foreach($pending as $check) {
      if(empty($cards[$check])) {
        $cards[$check] = $pendname;
      }
    }
    
    if ( empty($cards) ) { 
      echo '<p><em>There are currently no cards under this category.</em></p>'; 
    } 
    else {
      uksort($cards, 'strcasecmp');

      foreach($cards as $title=>$card) {
        $string = substr($title, 0, -2);
        foreach($array as $arr) {
          if ($string == $arr) {
            $card = trim($card);
            echo '<img src="'.$cardsurl.''.$card.'.'.$format.'" alt="" title="'.$title.'" /> ';
          }
        }
      }
    }
  }
