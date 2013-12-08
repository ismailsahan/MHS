var Profile = {};

Profile.Grades = {};
Profile.Academies = [];
Profile.Specialties = [];
Profile.Classes = [];
Profile.Leagues = [];
Profile.Organizations = [];
Profile.Departments = [];

Profile.getGrade = function() {
	if(Profile.Grades == []){
		$.get("{$_G['basefilename']}?action=api&operation=grade", {}, function(data){
			Profile.Grades = data;
			return data;
		});
	}else{
		return Profile.Grades;
	}
};

Profile.getGrade = function() {
	if(Profile.Academies == []){
		$.get("{$_G['basefilename']}?action=api&operation=grade", {}, function(data){
			Profile.Academies = data;
			return data;
		});
	}else{
		return Profile.Academies;
	}
};