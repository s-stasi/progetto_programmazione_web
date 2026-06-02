mvn clean package
cp target/lido.war $(brew --prefix tomcat)/libexec/webapps/
catalina run