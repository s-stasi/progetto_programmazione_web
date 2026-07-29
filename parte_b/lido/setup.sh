mvn clean package
cp build/lido_java8.war $(brew --prefix tomcat@9)/libexec/webapps/lido.war
$(brew --prefix tomcat@9)/bin/catalina run
# catalina run