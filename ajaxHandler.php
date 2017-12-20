<?php
header("Content-Type:application/json");
require('Suggestions.php');
require('database/PDOObject.php');


session_start();
session_regenerate_id();
$suggestions = new Suggestions();
//checks if the user is logged in
if(isset($_SESSION['userid']))
{
    
    //if there is a value in they keyword textbox
    if(isset($_GET['keywordInput']) && !empty($_GET['keywordInput'])){
        
        
        $userInput = $_GET['keywordInput'];
        
        $user = $_SESSION['userid'];
        
        $pdoObject = new PDOObject();
        $pdo = $pdoObject->getPDO();

        
        $ctr=retrieveUserSearchHistory($userInput,$user,$pdo);
               
       
        
        addCities($userInput,$pdo,$ctr,$user);
        
       
        
        echo json_encode($suggestions);
     
       
    }
}
//redirect user if they are not logged in
else {
    echo "<h3> Login or Register</h3><br>";
    echo '<a id="loginStyle" href=loginAction.php> Login </a><br>';
    echo '<a id="indeReg" href=registrationAction.php> Registration </a><br>';
}

/**
 * 
 * This function takes care of searching the database and looking if a past search
 * matches the input entered by the user. It will return a ctr so that the next
 * function (addCities) can know how many slots are still available otherwise you
 * will surpass 5.
 * @param type $userInput
 * @param type $user
 * @param type $pdo
 * @return array
 */
function retrieveUserSearchHistory($userInput,$user,$pdo)
{ try{
     global $suggestions;
        $query = "SELECT suggestion FROM userHistory WHERE suggestion LIKE ? AND userid = ? order by timeAdded DESC LIMIT 5;";
        $stmt = $pdo->prepare($query);
        
        $stmt->bindValue(1, $userInput.'%');
        $stmt->bindParam(2, $user);
        $ctr=0;
        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();
            foreach ($rows as $citySuggestion) {
                $suggestions->add($citySuggestion['suggestion']);
                $ctr++;
            }
        }
       return $ctr;
            
      
    } catch (PDOException $e) {
        $e->getMessage();
        exit;
    } finally {
        unset($pdo);
    }
}

/**
 * This function queries all cities that match the user's search. It will pass
 * through checkForDuplicates to see if there are any repeating data. Then, it 
 * will be added to the list if its not repeated otherwise it will go to the next
 * available data. Since we can only have 5 data we must have a counter. The reason
 * I limit it to 10 is that everything from userHistory could be repeated so I need
 * to get more data.
 * @param type $suggestions
 * @param type $userInput
 * @param type $count
 * @param type $pdo
 * @return type
 */
function addCities($userInput,$pdo,$ctr,$user)
{       try{
    global $suggestions;
            $query = "SELECT cityAndCountry FROM cities WHERE cityAndCountry LIKE ? LIMIT 10;";
            
            $stmt = $pdo -> prepare($query);
            $stmt->bindValue(1, $userInput.'%');
            if($stmt->execute()){
                $rows = $stmt->fetchAll();
                
                foreach($rows as $citySuggestion){
                    if($ctr<5)
                    {
                       if(!checkForDuplicates($userInput, $user, $pdo, $citySuggestion))
                       {
                            $suggestions -> add($citySuggestion['cityAndCountry']);
                            $ctr++;
                       }
                        
                    }
                        
                    
                }
            }
            
            return $suggestions;
        } catch (PDOException $e) {
            $e->getMessage();
            exit;
        } finally {
            unset($pdo);
        }
  }

  /**
   * This function will check if there are duplicates inside the userHistory table.
   * The reason im getting 10 from cityAndCountry is because it can happen that
   * everything from userHistory is duplicated in cityAndCountry. Therefore, to 
   * avoid displaying same data I check for duplicates and display data that is
   * not repeated.
   * @param type $userInput
   * @param type $user
   * @param type $pdo
   * @param type $citySuggestion
   * @return boolean
   */
  function checkForDuplicates($userInput,$user,$pdo,$citySuggestion)
  {
      try{
     
        $query = "SELECT suggestion FROM userHistory WHERE suggestion LIKE ? AND userid = ? order by timeAdded DESC LIMIT 5;";
        $stmt = $pdo->prepare($query);
        
        $stmt->bindValue(1, $userInput.'%');
        $stmt->bindParam(2, $user);
        $isDuplicate = false;
        if ($stmt->execute()) {
            $rows = $stmt->fetchAll();
            foreach($rows as $suggestion)
            {
                if($suggestion['suggestion'] === $citySuggestion['cityAndCountry'])
                    $isDuplicate=true;
            }
        }
            
            return $isDuplicate;
     
      }
      
      catch (PDOException $e) {
        $e->getMessage();
        exit;
    } finally {
        unset($pdo);
    }
  }
  
  




?>