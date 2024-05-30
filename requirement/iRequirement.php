<?php
    require '../Comment.php';
    
    interface iRequirement {
        //public getUserStories(); - When User Story is implemented
        public function getID();
        public function getHeading();
        public function getDescription();
        public function getPrioirity();
        public function getComments();
        public function getDependantRequirements();
        public function getImpactedRequirements();
        public function getSubrequirements();
        // get versions?

        // public function linkUserStory(UserStory&);
        // public function unlinkUserStory(UserStory&);

        public function appendComment($comment);
        public function addComment($id, $author, $date, $content);
        public function removeComment($comment);
        public function clearComments();

        public function markRequirementsToDependOn($requirement, $impactCoefficient);
        public function changeImpactCoefficientOnDependantRequirement($requirement, $newDependancyCoefficient);
        public function unmarkRequirementsToDependOn($requirement);
        public function clearDependanciesOnOtherRequirements();

        public function addDependantRequirement($requirement, $impactCoefficient);
        public function changeImpactCoefficientOnDependantOnThisRequirement($requirement, $newDependancyCoefficient);
        public function removeDependancyToRequirement($requirement);
        public function clearDependanciesOnThisRequirement();

        public function appendSubrequirement($requirement);
        public function addSubrequirement($id, $heading, $author, $content, $priority);
        public function removeSubrequirement($requirement);
        public function clearSubrequirements();
    }
?>